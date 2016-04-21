var PostService = new Class({

	downloadCsv: function () {
		window.location.href = '/posts/csv';
	},
	savePost: function (postData, onLoad) {
		$.ajax({
			url: 'posts',
			type: 'post',
			data: postData,
			success: function (r) {
				onLoad(true, r)
			}, error: function () {
				onLoad(false);
			}
		});
	},
	loadCounts: function (onLoad) {
		$.ajax({
			url: 'posts/counts',
			type: 'get',
			success: function (r) {
				onLoad(true, r)
			}, error: function () {
				onLoad(false);
			}
		});
	},
	loadPosts: function (offset, onLoad) {
		$.ajax({
			url: 'posts?offset='+offset,
			type: 'get',
			success: function (r) {
				onLoad(true, r)
			}, error: function () {
				onLoad(false);
			}
		});
	}
});
var services = {};
services.postService = new PostService();