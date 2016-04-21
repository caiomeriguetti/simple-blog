var BaseComponent = new Class({
	Extends: UIComponent,
	initialize: function (template, data) {
		this.parent(template, data);
		this.events = new EventDispatcher();
		this.model = AppModel.getInstance();
	}
});

var TopBar = new Class({
	Extends: BaseComponent,
	initialize: function (data) {
		this.parent('app/templates/top-bar.html', data);

		this.model.events.addEvent(AppModel.TOTALCOUNT_CHANGED, this.onChangeTotal.bind(this));
		this.model.events.addEvent(AppModel.VIEWSCOUNT_CHANGED, this.onChangeViews.bind(this));

		this.element.find('.app-bt-export').on('click', this.onClickExport.bind(this));
	},
	onChangeTotal: function (value) {
		this.element.find('.app-post-count').html(value);
	},
	onChangeViews: function (value) {
		this.element.find('.app-view-count').html(value);
	},
	onClickExport: function (e) {
		services.postService.downloadCsv();
	}
});

var ReplyBox = new Class({
	Extends: BaseComponent,
	initialize: function (data) {
		var self = this;
		this.parent('app/templates/reply-box.html', data);
		this.imageButton = this.element.find('.app-image-button');
		this.imageButton.click(this.onClickImage.bind(this));
		this.finishButton = this.element.find('.app-bt-finished');

		this.element.addClass("ReplyBox");
		this.element.find('iframe').on('load', this.onUploadDone.bind(this));
		this.element.find('.app-image-file').on('change', function () {
			self.element.find('form').submit();
			self.imageButton.button('loading');
		});

		this.element.on('click', '.app-bt-finished', this.onClickFinish.bind(this));

		this.model.events.addEvent(AppModel.PUBLISHING_CHANGED, this.onPublishingChanged.bind(this));
		$(window).on('scroll', this.onScroll.bind(this));
	},
	clear: function () {
		this.element.find('.app-title').val('');
		this.element.removeClass('upload-done');
	},
	getPostData: function () {
		var imageName = this.element.find('.app-post-image').attr('src').split('/').pop();
		var title = this.element.find('.app-title').val();
		return {'image': imageName, 'title': title};
	},
	onPublishingChanged: function (publishing) {
		if (publishing) {
			this.finishButton.button('loading');
			this.element.addClass('publishing');
		} else {
			this.finishButton.button('reset');
			this.element.removeClass('publishing');
		}
	},
	onScroll: function (e) {
		if ($(window).scrollTop() > 90) {
			this.element.addClass('fixed');
		} else {
			this.element.removeClass('fixed');
		}
	},
	onClickImage: function (e) {
		var self = this;
		this.element.find('.app-image-file').click();
	},
	onUploadDone: function (e) {
		this.imageButton.button('reset');
		this.element.find('.app-image-file').val('');
		var response;
		try {
			response = $.parseJSON(this.element.find('iframe').contents().text());
		} catch (e) {

		}

		if (!response) {
			return;
		}

		if (response.error === 1) {
			alert(response.text);
			return;
		}
		
		this.element.addClass("upload-done");
		this.element.find('.app-post-image').attr('src', response.url);
		
	},
	onClickFinish: function () {
		this.fireEvent(ReplyBox.CLICK_FINISH);
	}
});
ReplyBox.CLICK_FINISH = 'ReplyBox.CLICK_FINISH';

var PostBox = new Class({
	Extends: BaseComponent,
	initialize: function (data) {
		this.parent('app/templates/post-box.html', data);

		this.element.find('[data-datetime]').formatDateTime('mm/dd/y g:ii a');
	}
});

var PostList = new Class({
	Extends: BaseComponent,
	initialize: function (data) {
		this.parent('app/templates/post-list.html', data);

		this.model.events.addEvent(AppModel.POST_ADDED, this.onPostAdded.bind(this));
	},
	getLength: function () {
		return this.element.children().length;
	},
	onPostAdded: function (data, type) {
		var postBox = new PostBox(data);
		if (type === 'prepend') {
			this.prependChild(postBox);
		} else {
			this.addChild(postBox);
		}
	}
});

var AppComponent = new Class({
	Extends: BaseComponent,
	initialize: function (data) {
		this.parent('app/templates/app.html', data);

		this.replyBox = new ReplyBox({
			uploadEndpoint: 'posts/image'
		});
		this.replyBox.addEvent(ReplyBox.CLICK_FINISH, this.onClickFinish.bind(this));

		this.postList = new PostList();
		this.topBar = new TopBar();

		this.addChild(this.topBar);
		this.addChild(this.replyBox);
		this.addChild(this.postList);

		services.postService.loadPosts(0, this.onLoadPosts.bind(this));

		services.postService.loadCounts(this.onLoadCounts.bind(this));

		$(window).on('scroll', this.onScroll.bind(this));
	},
	onLoadCounts: function (success, data) {
		var self = this;
		this.countsLoaded = true;
		this.model.setTotalCount(data.posts);
		this.model.setViewsCount(data.views);

		setTimeout(function () {
			services.postService.loadCounts(self.onLoadCounts.bind(self));
		}, 15000);
	},
	onLoadPosts: function (success, data) {
		var self = this;
		if (data.hasNext === 0) {
			this.endOfList = true;
		}

		$(data.posts).each(function (index, item) {
			self.model.addPost(item, 'append');
		});
		this.loadingPosts = false;
	},
	onScroll: function () {
		if (this.endOfList === true) {
			return;
		}

		var self = this;
		var scrollHeight = $(document).height();
		var scrollPosition = $(window).height() + $(window).scrollTop();
		if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
			if (self.loadingPosts === true) {
				return;
			}
		  services.postService.loadPosts(self.postList.getLength(), this.onLoadPosts.bind(this));
		  this.loadingPosts = true;
		}
	},
	onClickFinish: function () {
		var self = this;
		services.postService.savePost(this.replyBox.getPostData(), function (success, response) {
			self.model.setPublishing(false);
			if (success) {
				if (response.error === 1) {
					alert(response.text);
					return;
				}

				self.model.addPost(response, 'prepend');
				self.replyBox.clear();
				

				if (self.countsLoaded === true) {
					self.model.incrementTotalCount();
				}

				if ($('body').scrollTop()  > 0) {
					$('body').animate({scrollTop: 0});
				}
			}
		});

		self.model.setPublishing(true);
	}
});