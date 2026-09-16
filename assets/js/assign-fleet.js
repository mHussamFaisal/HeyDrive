

(function ($) {
    "use strict";

    var AssignFleet = function (options) {
        this.init('assign_fleet', options, AssignFleet.defaults);
    };

    $.fn.editableutils.inherit(AssignFleet, $.fn.editabletypes.list);

    $.extend(AssignFleet.prototype, {
        fillItems: function($el, data) {
            var that = this;
            var attr;
            $el.html('');
            if($.isArray(data)) {
                for(var i=0; i<data.length; i++) {
                    attr = {};
                    if(data[i].children) {
                        attr.label = data[i].text;
                        $el.append(that.fillItems($('<optgroup>', attr), data[i].children));
                    } else {
                        attr.value = data[i].value;
                        if(data[i].disabled) {
                            attr.disabled = true;
                        }
                        $el.append($('<option>', attr).text(data[i].text));
                    }
                }
            }
            return $el;
        },
        updateInfo: function() {
            var that = this;
            var commission = that.$input.filter('[name="commission"]');
            var commissionHtml = that.formData.commission_percent +'%';

            if (that.formData.commission_calculated >= 0 && parseFloat(commission.val()) != parseFloat(that.formData.commission_calculated)) {
                commissionHtml += ' | '+ that.formData.commission_calculated;
            }

            if (commissionHtml) {
                commissionHtml = '<span onclick="$(\'.editable-assign_fleet-commission input[name=commission]\').val('+ that.formData.commission_calculated +').change();" style="cursor:pointer; margin-left:5px;" title="Use this auto calculated value">('+ commissionHtml +')</span>';
            }

            $('.editable-assign_fleet-commission .info-html').html(commissionHtml);
        },
        renderList: function() {
            this.$input = this.$tpl.find('input, select');

            this.formData = {
                fleet: 0,
                total_price: 0,
                commission: 0,
                commission_calculated: 0,
                commission_percent: 0,
            };

            var that = this;
            var fleet = that.$input.filter('[name="fleet"]');
            var commission = that.$input.filter('[name="commission"]');
            var options = commission.closest('div.editable-assign_fleet-options');

            that.fillItems(fleet, that.sourceData);

            fleet.change(function() {
                var fleetId = parseInt($(this).val());

                $.each(that.sourceData, function(index, item) {
                    if (parseInt(item.value) == fleetId) {
                        that.formData.commission_percent = parseFloat(item.commission).toFixed(2);
                        that.formData.commission_calculated = ((that.formData.total_price / 100) * that.formData.commission_percent).toFixed(2);
                        return false;
                    }
                });

                commission.val(that.formData.commission > 0 ? that.formData.commission : that.formData.commission_calculated);
                that.updateInfo();

                if (fleetId > 0) {
                    options.removeClass('hidden');
                }
                else {
                    options.addClass('hidden');
                }
            });

            commission.change(function() {
                that.updateInfo();
            });

            this.$input.filter('[name="fleet"]').on('keydown.editable', function(e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        },
        html2value: function(html) {
            return null;
        },
        value2str: function(value) {
            var str = '';

            if(value) {
                for(var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }

            return str;
        },
        str2value: function(str) {
            return str;
        },
        value2input: function(value) {
            if (!value) { return; }

            this.formData.fleet = parseInt(value.fleet);
            this.formData.total_price = parseFloat(value.total_price).toFixed(2);
            this.formData.commission = parseFloat(value.commission).toFixed(2);

            this.$input.filter('[name="fleet"]').val(this.formData.fleet).change();
            this.$input.filter('[name="commission"]').val(this.formData.commission).change();
        },
        input2value: function() {
            return {
                fleet: this.$input.filter('[name="fleet"]').val(),
                commission: this.$input.filter('[name="commission"]').val(),
            };
        },
        activate: function() {
            this.$input.filter('[name="fleet"]').focus();
        },
        autosubmit: function() {
            this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });

    AssignFleet.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
        tpl: '<div class="editable-assign_fleet"><select name="fleet" class="form-control input-sm"><option value="0">Assign Fleet</option></select></div>'+
             '<div class="editable-assign_fleet-options hidden">'+
                 '<div class="editable-assign_fleet editable-assign_fleet-commission clearfix"><span class="title">Fleet income:</span><input type="text" name="commission" value="" placeholder="0.00" class="form-control input-sm" style="width:70px; float:right;"></div>'+
             '<div>',
        inputclass: ''
    });

    $.fn.editabletypes.assign_fleet = AssignFleet;

}(window.jQuery));
