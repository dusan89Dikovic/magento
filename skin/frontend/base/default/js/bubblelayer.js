var BubbleLayer = Class.create();
BubbleLayer.prototype = {
    initialize: function(options) {
        this.options = Object.extend({
            leftSelector:       '.col-left',
            contentSelector:    '.col-main',
            scrollSelector:     '.nav-container',
            priceSlider:        'price-slider',
            priceVar:           'price',
            enableAjax:         true,
            enableAutoScroll:   true,
            enableHistory:      true
        }, options || {});

        if (typeof(History) === 'undefined' || !this.options.enableAjax) {
            this.options.enableHistory = false;
        }

        var o = this.options;

        this.handlePriceSlider();

        if (o.enableHistory && this.getLeftElement() && this.getContentElement()) {
            document.observe('dom:loaded', function() {
                this.saveState(
                    document.location.href,
                    this.getLeftElement().innerHTML,
                    this.getContentElement().innerHTML,
                    document.title
                );
            }.bind(this));
        }

        document.observe('click', function(e) {
            var el = e.element();
            if (el.hasClassName('show-hidden')) {
                e.stop();
                el.up(1).select('li.hideable').invoke('toggleClassName', 'no-display');
                el.up(1).select('.show-hidden').invoke('toggleClassName', 'no-display');
            } else if (e.findElement('.block-layered-nav a') || e.findElement('.toolbar a') || e.findElement('.checkbox-filter')) {
                var url;
                if (el.tagName == 'INPUT') {
                    url = el.value;
                } else if (!el.href && el.up('a')) {
                    el = el.up('a');
                }
                if (!url && el.href) {
                    url = el.href;
                    e.stop();
                }
                if (url) {
                    this.handleLayer(url);
                }
            }
        }.bind(this));

        if (o.enableHistory) {
            History.Adapter.bind(window, 'statechange', function() {
                var state   = History.getState();
                var data    = state.data;
                this.updateContent(data.left, data.content);
                this.handlePriceSlider();
            }.bind(this));
        }
    },
    getLeftElement: function() {
        return $$(this.options.leftSelector).shift();
    },
    getContentElement: function() {
        return $$(this.options.contentSelector).shift();
    },
    getScrollElement: function() {
        return $$(this.options.scrollSelector).shift();
    },
    setCurrentUrl: function(url) {
        this.options.currentUrl = url;
    },
    saveState: function(url, left, content, title) {
        if (this.options.enableHistory) {
            var state = {
                left: left,
                content: content
            };
            History.pushState(state, title, url);
        }
    },
    scroll: function() {
        var scrollEl = this.getScrollElement();
        if (this.options.enableAutoScroll && scrollEl) {
            new Effect.ScrollTo(scrollEl);
        }
    },
    updateContent: function(left, content) {
        var leftEl = this.getLeftElement();
        var contentEl = this.getContentElement();
        if (leftEl) {
            leftEl.update(left);
        }
        if (contentEl) {
            contentEl.update(content);
        }
    },
    handleLayer: function(url) {
        if (!this.options.enableAjax) {
            setLocation(url);
        } else {
            new Ajax.Request(url, {
                method: 'get',
                onCreate: function() {
                    if ($$('.col-wrapper')[0]) {
                        $$('.col-wrapper')[0].style.opacity = .5;
                    }
                },
                onSuccess: function(response) {
                    var result  = response.responseJSON;
                    if (result.left && result.content) {
                        this.updateContent(result.left, result.content);
                        this.saveState(url, result.left, result.content, result.title);
                        this.handlePriceSlider();
                        this.scroll();
                    }
                    if ($$('.col-wrapper')[0]) {
                        $$('.col-wrapper')[0].style.opacity = 1;
                    }
                    this.setCurrentUrl(url);
                }.bind(this),
                onFailure: function() {
                    setLocation(url);
                }
            });
        }
    },
    handlePriceSlider: function() {
        var slider = $(this.options.priceSlider);
        if (slider) {
            var self = this;
            var handles = slider.select('.handle');
            var BubblePriceSlider = new Control.Slider(handles, slider, {
                range: $R(parseInt($('price-min').value), parseInt($('price-max').value)),
                sliderValue: [parseInt($('price-value-min').value), parseInt($('price-value-max').value)],
                restricted: true,
                spans: slider.select('.span'),
                onSlide: function(v) {
                    if (!isNaN(v[0])) {
                        $$('.price-range span.price')[0].update(formatCurrency(v[0], self.options.priceFormat, false));
                    }
                    if (!isNaN(v[1])) {
                        $$('.price-range span.price')[1].update(formatCurrency(v[1], self.options.priceFormat, false));
                    }
                },
                onChange: function(v) {
                    var min = v[0].toFixed();
                    if (min == 0 || isNaN(min)) {
                        min = '';
                    }
                    var max = v[1].toFixed();
                    if (max >= this.range.end || isNaN(max)) {
                        max = '';
                    }
                    var param = self.options.priceVar + '=' + min + '-' + max;
                    var pattern = self.options.priceVar + '=\\d*-\\d*';
                    var url = self.options.currentUrl;
                    if (!min && !max) {
                        url = url.replace(new RegExp(pattern), '');
                    } else if (url.match(new RegExp(pattern))) {
                        url = url.replace(new RegExp(pattern), param);
                    } else {
                        url += url.match(new RegExp('\\?')) ? '&' : '?';
                        url += param;
                    }
                    self.handleLayer(url.replace(/[&\?]+$/g, ''));
                }
            });

            // Handle mouseover on slider to modify the closest handle on click
            slider.observe('mousemove', function(event) {
                var posHandleL = handles[0].cumulativeOffset().left;
                var posHandleR = handles[1].cumulativeOffset().left;
                var posPointer = Event.pointerX(event);
                if (posPointer > posHandleR || posPointer > ((posHandleR + posHandleL) / 2)) {
                    BubblePriceSlider.activeHandle = handles[1];
                    BubblePriceSlider.activeHandleIdx = 1;
                } else {
                    BubblePriceSlider.activeHandle = handles[0];
                    BubblePriceSlider.activeHandleIdx = 0;
                }
                BubblePriceSlider.updateStyles();
            });
        }
    }
};