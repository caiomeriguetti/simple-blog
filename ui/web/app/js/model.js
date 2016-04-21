var AppModel = new Class({
	initialize: function () {
		this.events = new EventDispatcher();
		this.posts = [];
		this.publishing = false;
		this.totalCount = 0;
		this.viewsCount = 0;
	},
	addPost: function (data, type) {
		if (type === 'prepend') {
			this.posts.unshift(data);
		} else {
			this.posts.push(data);
		}
		this.events.fireEvent(AppModel.POST_ADDED, [data, type]);
	},
	incrementTotalCount: function () {
		this.setTotalCount(this.totalCount + 1);
	},
	setTotalCount: function (value) {
		this.totalCount = value;
		this.events.fireEvent(AppModel.TOTALCOUNT_CHANGED, value);
	},
	setViewsCount: function (value) {
		this.viewsCount = value;
		this.events.fireEvent(AppModel.VIEWSCOUNT_CHANGED, value);
	},
	setPublishing: function (isPublishing) {
		if (this.publishing === isPublishing) {
			return;
		}

		this.publishing = isPublishing;
		this.events.fireEvent(AppModel.PUBLISHING_CHANGED, this.publishing);
	}
});
AppModel.POST_ADDED = 'AppModel.POST_ADDED';
AppModel.PUBLISHING_CHANGED = 'AppModel.PUBLISHING_CHANGED';
AppModel.TOTALCOUNT_CHANGED = 'AppModel.TOTALCOUNT_CHANGED';
AppModel.VIEWSCOUNT_CHANGED = 'AppModel.VIEWSCOUNT_CHANGED';
AppModel.getInstance = function () {
	if (!AppModel.instance) {
		AppModel.instance = new AppModel();
	}

	return AppModel.instance;
};
