/*
 * Jquery Quickedit 中文叫快速修改插件
 * 使用例子:
 * $('.table a').quickEdit({
 *  blur: false,
 *  checkold: true,
 *  space: false,
 *  maxLength: 50,
 *  showbtn: false,
 *  submit: function (dom, newValue) {
 *      dom.text(newValue);
 *  }
 * });
 */
(function ($) {
    $.quickEdit = {
        defaults: {
        	id : false,
            prefix: '[qe=?]',
            oldvalue: '', //原始值
            blur: false, //离开是否处理
            autosubmit: true, //离开后是否默认提交
            checkold: true, //是否检测与原始值相同
            space: false, //为空是否提交
            maxlength: false, //文本最大长度
            showbtn: true, //显示按钮
            submit: function () {
            },
            cancel: function () {
            },
            tmpl: '<span qe="scope"><span><input class="ui fluid input" type="text" qe="input"/></span>'
            + '<span><button qe="submit" >Okey</button>'
            + '<button qe="cancel">Cancel</button></span></span>'
        },

        init: function (dom, options) {
            if (!this.check(dom, options)) {
                return;
            }
            this.options = $.extend({}, this.defaults, options);
            this.dom = dom.hide();
            this.create();
            this.initEvent();
            return this.quickEdit;
        },

        check: function (dom) {
            if (this.quickEdit) {
                if (this.options.blur) {
                    this.options.autosubmit && this.submit() || this.cancel();
                } else {
                    this.hook = dom;
                    return;
                }
            }
            return true;
        },

        //选择条件
        select: function (type) {
            return this.options.prefix.replace('?', type);
        },

        //创建修改
        create: function () {
            var oldvalue = this.options.oldvalue;
            if (!oldvalue.length) {
                oldvalue = this.dom.text();
            }
            var quickEdit = $(this.options.tmpl).eq(0);
            quickEdit.find(this.select('input')).val(oldvalue);
            if (!this.options.showbtn) {
                this.options.blur = true;
                this.options.autosubmit = true;
                quickEdit.find(this.select('submit')).remove();
                quickEdit.find(this.select('cancel')).remove();
            }
            this.quickEdit = quickEdit;
        },

        submit: function () {
        	var id = this.options.id;
            var self = this,
                options = self.options;

            var newvalue = $.trim($(this.select('input'), self.quickEdit).val());
            if ((newvalue.length || options.space) && (newvalue != options.oldvalue || !options.checkold)) {
                if ($.isFunction(options.submit)) {
                    if (options.submit(self.dom, newvalue,id) !== false) {
                        self.cancel(true);
                    }
                }
            } else {
                self.cancel();
            }
        },

        cancel: function (nocall) {
            var self = this;
            if (!self.quickEdit) {
                return;
            }
            var cancel = function () {
                self.quickEdit.remove();
                self.quickEdit = undefined;
                self.dom.show();
                if (self.hook) {
                    self.hook.trigger('click');
                    self.hook = undefined;
                }
            };
            if (!nocall && $.isFunction(this.options.cancel)) {
                if (this.options.cancel(this.dom) !== false && this.quickEdit) {
                    cancel();
                }
            } else {
                cancel();
            }
        },

        initEvent: function () {
            var self = this,
                scope = self.quickEdit;

            //点击提交
            scope.off('click.qe');
            scope.on('click.qe', self.select('submit'), function (e) {
                self.submit();
                e.stopPropagation();
            });

            //点击取消
            scope.on('click.qe', self.select('cancel'), function (e) {
                self.cancel();
                e.stopPropagation();
            });

            //点击文本框
            scope.on('click.qe', self.select('input'), function (e) {
                e.stopPropagation();
            });

            //回车事件监控
            scope.off('keydown.edit').on('keydown.edit', self.select('input'), function (e) {
                if (e.keyCode == 13) {
                    self.submit();
                    return false;
                }
                if (self.options.maxlength && $(this).val().length > self.options.maxlength) {
                    $(this).val($(this).val().substr(0, self.options.maxlength));
                }
            });

            //点击其他地方
            $(document).off('click.qe').on('click.qe', function (e) {
                if (e.target == self.dom.get(0) || $(e.target).is(self.select('scope')) || $(e.target).parents(self.select('scope')).length) {
                    return;
                }
                if (self.options.blur) {
                    self.options.autosubmit && self.submit() || self.cancel();
                }
            });
        }
    };

    $.fn.quickEdit = function (arg1, arg2) {
        if (typeof arg1 == 'string') {
            switch (arg1) {
                case 'submit':
                    $.quickEdit.submit();
                    break;
                case 'cancel':
                    $.quickEdit.cancel();
                    break;
                case 'create':
                    return $.quickEdit.init($(this), arg2);
                    break;
            }
        } else {
            $(this).on('click', function () {
                var edit = $.quickEdit.init($(this), arg1);
                if (edit) {
                    $(this).after(edit);
                    $('input', edit)[0].select();
                }
            });
        }
    };
})(jQuery);