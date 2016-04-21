var templates = {};
function loadTemplate (name, onLoad) {
	if (!name) {
		return;
	}

	$.get(name, function(template) {
		templates[name] = template;
		Mustache.parse(template);
		onLoad();
  });
}

function loadTemplates (list, onLoad) {
	var k = 0;
	var onLoadTemplate = function () {
		k++;
		if (k == list.length) {
			onLoad();
		} else {
			loadTemplate(list[k], onLoadTemplate);
		}
	};
	loadTemplate(list[k], onLoadTemplate);
}

function renderTemplate (name, data) {
	if (!data) {
		data = {};
	}
	return Mustache.render(templates[name], data)
}

var EventDispatcher = new Class({
	initialize: function () {
		this.listeners = [];
	},
	addEvent: function (name, listener) {
		this.listeners.push({name: name, listener: listener})
	},
	fireEvent: function (name, data) {
		$(this.listeners).each(function (index, item) {
			if (name === item.name) {
				if(!(Object.prototype.toString.call( data ) === '[object Array]')) {
					data = [data];
				}
				item.listener.apply(this, data);
			}
		});
	}
});

var UIComponent = new Class({
	initialize: function (template, data) {
		if (!data) {
			data = {};
		}
		this.data = data;
		this.template = template;
		this.element = $(renderTemplate(template, data));
		this.childs = [];
		this.listeners = [];
		this.element.data("uicomponent", this);
		this.events = new EventDispatcher();
	},
	update: function () {
		var rendered = renderTemplate(this.template, this.data);
		var newElement = $(rendered);
		this.element.children().remove();
		this.element.html(newElement.html());
	},
	addChild: function (component) {
		this.childs.push(component);
		this.element.append(component.element);
	},
	prependChild: function (component) {
		this.childs.unshift(component);
		this.element.prepend(component.element);
	}, 
	removeAllChilds: function () {
		this.childs = [];
		this.element.children().remove();
	},
	addEvent: function (name, listener) {
		this.events.addEvent(name, listener);
	},
	fireEvent: function (name, data) {
		this.events.fireEvent(name, data);
	}
});