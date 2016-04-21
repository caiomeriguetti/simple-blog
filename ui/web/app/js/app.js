function init () {
	var appComponent = new AppComponent();
	$('.app').append(appComponent.element);
}

$(function () {
	loadTemplates([
		'app/templates/app.html',
		'app/templates/top-bar.html',
		'app/templates/reply-box.html',
		'app/templates/post-list.html',
		'app/templates/post-box.html'
	], function () {
		if (init) {
			init();
		}
	});
});
