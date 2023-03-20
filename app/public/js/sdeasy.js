/**
 * @file <SDEasy> This is my cool script
 * @author Mahmut YILDIRIM a.k.a SDClowen <cloweninc@gmail.com>
 * @copyright Mahmut YILDIRIM a.k.a SDClowen 2022
 */

$(function () {

    /**
     * Show active class on the linked tag after which page is viewed
     */
    if (location.pathname)
    {
        let pathElement = $("a[href='" + location.pathname + "']");
        pathElement.addClass("active").attr("href", "#").attr("onclick", "return false");
    }

    /**
     * Show error message with alert lazy:run(); :=)
     */
    function error() {
        alert("There was a problem and the action could not be processed. Please try again later.");
    }

    /**
     * Active spinner animation on the element
     */
    $.fn.spinner = function () {
        this.toggleClass("spinner");
    };

    /**
     * Show message on the element
     * @param {*} obj  the json variable
     */
    $.fn.message = function (obj) {
        if (!obj.message)
            return;

        var msg = $(`<div class='alert alert-${obj.type} alert-dismissible' style="display: none" role="alert">${obj.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`);
        this.append(msg);

        let readingTimeRemaining = (msg.text().length * 60 / 24) * 0.13 * 100 + 2000;

        msg.slideDown().delay(readingTimeRemaining).slideUp("slow", function () { $(this).remove(); });
    }

    /**
     * Go to the specified element in an animated way
     * @param {*} main Is the main element from which the animation will start.
     */
    $.fn.scroll = function (main = 'html, body') {
        var position = this.offset().top;
        $(main).animate({
            scrollTop: position
        }, 500, 'linear');
    }

    function updateTableIndexes(table) {
        let index = 0;
        let array = $(table + " tr th[scope=row]").toArray();
        array.forEach(row => {
            $(row).html(++index);
        });
    }

    function sdajax($this, spinnerObj, onAjaxDone, xhrUrl, xhrData = false, xhrType = "get", xhrDataType = "json") {
        spinnerObj.spinner();

        const content = $($this.data("content"));
        let contentType = $this.data("content-type");
        if (!contentType)
            contentType = "alert";

        const remove = $this.data("remove");
        let redirect = $this.attr("redirect");
        const autoUpdate = $this.data("auto-update");
        const autoUpdateType = $this.data("auto-update-type");
        const injectCode = $this.data("run");

        const insertTo = $this.data("insert-to");
        const insertType = $this.data("insert-type");
        const modalDispose = $this.data("dispose-modal");

        if (redirect)
            redirect = redirect.split(":");

        let settings = {};
        settings.type = xhrType;
        settings.url = xhrUrl;
        settings.dataType = xhrDataType;
        settings.data = xhrData;
        settings.processData = false;
        settings.contentType = false;
        settings.success = function (data) {

            if (remove && data.type == "success")
                $(remove).fadeOut("fast", function () { $(this).remove() });

            if (insertTo && data.type == "success" && !autoUpdate) {
                if (!insertType || insertType == "prepend")
                    $(data.message).prependTo(insertTo).fadeIn("slow");
                else
                    $(data.message).appendTo(insertTo).fadeIn("slow");
            }
            else if (content.length && contentType == "html" && xhrDataType == "html") {
                content.scroll();
                content.html(data);
            }
            else if (content.length && data.message) {
                if (contentType == "html")
                    content.html(data.message);
                else {
                    content.message(data);
                    content.scroll(content.parent());
                }
            }

            if (data.type == "success") {

                if (modalDispose)
                    $(modalDispose).modal('hide');

                if (autoUpdate) {
                    const target = $this.data("parent");
                    if (autoUpdateType == "one")
                        $this.data("auto-update", "false");

                    for (const [key, value] of Object.entries(data.message)) {
                        let jsonDomItem = $(`[data-field=${key}]`, target);
                        if (jsonDomItem.length) {
                            const updateType = jsonDomItem.data("update-type");
                            const then = jsonDomItem.data("field-then");
                            if(typeof(then) !== "undefined")
                            {
                                if(data.message[then])
                                    jsonDomItem.show();
                                else
                                    jsonDomItem.hide();
                            }

                            switch (updateType) {
                                case "img":
                                    jsonDomItem.find("img").attr("src", value);
                                    break;
                                case "json":
                                    jsonDomItem.data("json", value);
                                    break;
                                default:
                                    jsonDomItem.html(value);
                                    break;
                            }
                        }
                    }
                }

                if (data.scrollTo)
                    $(window).scrollTop($(data.scrollTo).offset().top);

                if (injectCode)
                    eval(injectCode);

                if (redirect)
                    setTimeout(function () { window.location.href = redirect[0]; }, redirect[1]);
            }

            if (data.redirect) {
                redirect = data.redirect.split(":");
                if(redirect == "REFRESH-PAGE")
                    location.reload();
                else
                setTimeout(function () { window.location.href = redirect[0]; }, redirect[1]);
            }

            onAjaxDone?.(data);
        };

        $.ajax(settings)
            .always(function () { spinnerObj.spinner(); })
            .fail(function () { error() });
    }

    /**
     * Operates according to the conditions specified on all elements with the {data-url} tag. lazy:run(); :=)
     */
    $("[lazy-load]").each(function () {

        const $this = $(this);

        let url = $this.attr("lazy-load");
        let actionType = $this.data("type");

        if (!actionType)
            actionType = "get";

        sdajax($this, $this, null, url, false, actionType);

        //return false;
    });

    /**
     * Operates according to the conditions specified on all elements with the {data-url} tag. lazy:run(); :=)
     */
    $("body").on("click", "[data-url]", function () {

        const $this = $(this);
        let actionType = $this.data("action-type");
        if (!actionType)
            actionType = "get";

        let actionDataType = $this.data("type");
        if (!actionDataType)
            actionDataType = "json";

        let url = $this.data("url");
        let countable = $this.data("countable");

        const hasCountable = typeof(countable) !== "undefined";
        let num = 0;
        if(hasCountable)
        {
            num = parseInt(countable) + 1;
            url = url + "/" + num;
        }

        sdajax($this, $this, function(data)
        {
            if(hasCountable)
                $this.data("countable", num);
                
        }, url, false, actionType, actionDataType);

        return false;
    });

    /**
     * Operates with ajax on all form elements lazy:run();
     */
    $('[role="form"]').on('submit', function (event) {
        if (!event.isDefaultPrevented()) {
            event.preventDefault();

            const $this = $(this);
            const method = event.currentTarget.method;
            const action = event.currentTarget.action;

            let button = null;
            if (event.hasOwnProperty("originalEvent"))
                button = $(event.originalEvent.submitter);
            else
                button = event.handleObj.handler.arguments[1].submitter;

            sdajax($this, button, function (data) {
                if (data.type == "success") {
                    $this[0].reset();

                    // todo reset tom-select
                }
            }, action, new FormData(this)/*$(this).serialize()*/, method);

            return false;
        }
    });

    /**
     * When the a tag is clicked on the charts, it updates with ajax. lazy:run(); :=)
     */
    $('[sd-chart-ajax] a').click(function (event) {
        if (event.isDefaultPrevented())
            return false;

        event.preventDefault();

        var $this = $(this);
        var $parent = $(this).parent();

        if ($this.attr("active"))
            return;

        $parent.find("a").removeClass("active");
        $this.addClass("active");

        var action = $parent.attr("sd-chart-ajax");
        var prefix = $parent.data("prefix");
        var chart = $parent.data("chart");
        var period = $this.attr("sd-chart-period");
        var $update = $(`#${prefix}update`);

        $update.spinner();

        $.ajax({
            type: "post",
            url: action,
            data: { period },
            dataType: "json",
            success: function (data) {
                if (data.type == "success") {
                    $update.text($this.text());

                    $(`#${prefix}result`).text(data.message.result);

                    if (data.message.percent < 0) {
                        $(`#${prefix}percent`).removeClass().addClass("text-danger");
                        $(`#${prefix}percent svg`).html('<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline>');
                    }
                    else if (data.message.percent > 0) {
                        $(`#${prefix}percent`).removeClass().addClass("text-success");
                        $(`#${prefix}percent svg`).html('<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>');
                    }
                    else {
                        $(`#${prefix}percent`).removeClass().addClass("text-warning");
                        $(`#${prefix}percent svg`).html('<line x1="5" y1="12" x2="19" y2="12"></line>');
                    }

                    $(`#${prefix}percent span`).text(`${data.message.percent}%`);

                    ApexCharts.exec(chart, 'updateOptions', data.message.chart, false, true);
                }
                else
                    error();
            }
        })
            .always(() => {
                $update.spinner();
            });
    });

    /**
     * Auto fill the form inputs in the modal from the json
     */
    $('[role=dialog]').on('show.bs.modal', function (event) {
        const target = $(event.relatedTarget);
        const modal = $(this);
        const titleContent = modal.find('.modal-title');
        const title = target.data('title');
        if (title)
            titleContent.text(title);

        const action = target.data("action");
        if (action) {
            const form = modal.find("form");
            form[0].reset();

            const targetAutoUpdate = target.data("auto-update");
            if (targetAutoUpdate) {
                const targetParent = target.data("parent");
                form.data("auto-update", targetAutoUpdate);
                form.data("parent", targetParent);
            }

            const selectize = form.find("select[selectize]");
            if (selectize.length)
                selectize.selectize()[0].selectize.setValue(0);

            form.attr("action", action);

            let jsonObj = target.data("json");

            const fn = function () {
                
                if (jsonObj) {
                    for (const [name, value] of Object.entries(jsonObj)) {
                        console.log(`${name}    ${value}`);
                        let item = $('[name="' + name + '"]', form);

                        if (item.is("select")) {
                            var index = item.find('[value="' + value + '"]');
                            index.prop('selected', true);
                            
                            item.trigger("change");   

                            try {
                                item[0].tomselect.sync();
                            } catch (error) {

                            }
                        }
                        else if (item.is("input")) {
                            var type = item.attr('type');

                            switch (type) {
                                case 'checkbox':
                                    item.attr('checked', value > 0);
                                    break;
                                case 'radio':
                                    var id = item.find('[value="' + value + '"]');
                                    id.attr('checked', 'checked');
                                    break;
                                case 'file':
                                    break;
                                default:
                                    item.val(value);
                                    break;
                            }

                            item.trigger("change");
                        }
                        else if (item.attr("tinymce")) {
                            var editor = tinymce.get(name);
                            if (editor != null)
                            {
                                editor.setContent(value);
                                tinymce.triggerSave();
                            }
                        }
                        else if (item.is("textarea")) {
                            item.html(value);
                            item.trigger("change");
                        }
                        else if (item.attr("selectize")) {
                            item.selectize()[0].selectize.setValue(value);
                        }
                    }
                }
            };

            const step2 = function () {
                const lazyData = form.find("[modal-lazy-data]");
                if (lazyData.length) {
                    const totalLength = lazyData.length;
                    
                    let ajaxs = [];

                    lazyData.each(function () {
                        const $this = $(this);

                        const lazyUrl = $this.attr("modal-lazy-data");
                        ajaxs.push($.ajax({
                            url: lazyUrl,
                            type: "GET",
                            dataType: "html",
                            success: function (ajaxResult) {
                                $this.html(ajaxResult);
                                console.log(lazyUrl);
                            }
                        }));
                    });

                    $.when.apply(this, ajaxs).done(function(){
                        console.log("All ajax done!");
                        fn();
                    });
                }
                else
                    fn();
            };

            let getJson = target.data("get-json");
            if (getJson) {
                $.ajax({
                    url: getJson,
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        jsonObj = data;
                        step2();
                    }
                });
            }
            else
                step2();
        }
    });

    $("[submit]").on("click", function (event) {
        let data = $($(this).attr("submit"));
        data.trigger('submit', { submitter: $(this) });
    });

    $("input[data-input-toggle]").on("change", function () {
        var value = $(this).data("input-toggle");

        if ($(this).prop("checked"))
            $(value).slideDown("fast");
        else
            $(value).slideUp("fast");
    });

    $("[data-href]").on("click", function(){
        window.location = $(this).data("href");
    });

    $("[data-toggle]").change(function () {
        var value = $(this).data("toggle");
        $(value).fadeToggle();
    });

    $("[data-trigger = 'true']").each(function () {
        $(this).trigger('click');
    });

    $("[avatar-selector]").on("change", function () {
        let val = $(this).val();
        console.log(val);
    });

    $("[data-format]").on("change", function () {
        const $this = $(this);
        const dom = $($this.data("format"));
        const dataCurrency = $this.data("currency");
        // Create our number formatter.
        var formatter = new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: dataCurrency,
        });

        const value = formatter.format($this.val());
        dom.html(value.substring(1, value.length));
    });

    $("[data-fill]").change(function () {

        const $this = $(this);
        let actionType = $this.data("action-type");
        if (!actionType)
            actionType = "get";

        let actionDataType = $this.data("type");
        if (!actionDataType)
            actionDataType = "json";

        $.ajax({
            url: `${$this.data("fill")}/${$this.val()}`,
            type: "GET",
            dataType: "html",
            async : false,
            success: function (data) {
                const obj = $($this.data("fill-to"));
                obj.html(data);
            }
        });
    });
});
