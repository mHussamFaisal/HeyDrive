

(function ($) {
    "use strict";

    var AssignDriver = function (options) {
        this.init('assign_driver', options, AssignDriver.defaults);
    };

    $.fn.editableutils.inherit(AssignDriver, $.fn.editabletypes.list);

    $.extend(AssignDriver.prototype, {
        getVehicleSource: function(id) {
            var that = this;
            var vehicle = that.$input.filter('[name="vehicle"]');

            $.ajax({
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: EasyTaxiOffice.appPath +'/admin/bookings/inline-editing/vehicle_list?id='+id,
                type: 'GET',
                dataType: 'json',
                async: false,
                cache: false,
                data: {},
                success: function(response) {
                    that.fillItems(vehicle, response);
                    var container = $('.editable-assign_driver-vehicle');

                    if (response.length > 1) {
                        container.removeClass('hidden');
                    }
                    else {
                        container.addClass('hidden');
                    }
                },
                error: function(response) {
                    alert('Error: '+ response);
                }
            });
        },
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
            var cash = that.$input.filter('[name="cash"]');
            var commissionHtml = that.formData.commission_percent +'%';
            var cashHtml = '';

            if (that.formData.commission_calculated >= 0 && parseFloat(commission.val()) != parseFloat(that.formData.commission_calculated)) {
                commissionHtml += ' | '+ that.formData.commission_calculated;
            }

            if (that.formData.cash_calculated >= 0 && parseFloat(cash.val()) != parseFloat(that.formData.cash_calculated)) {
                cashHtml += that.formData.cash_calculated;
            }

            if (commissionHtml) {
                commissionHtml = '<span onclick="$(\'.editable-assign_driver-commission input[name=commission]\').val('+ that.formData.commission_calculated +').change();" style="cursor:pointer; margin-left:5px;" title="Use this auto calculated value">('+ commissionHtml +')</span>';
            }

            if (cashHtml) {
                cashHtml = '<span onclick="$(\'.editable-assign_driver-cash input[name=cash]\').val('+ that.formData.cash_calculated +').change();" style="cursor:pointer; margin-left:5px;" title="Use this auto calculated value">('+ cashHtml +')</span>';
            }

            $('.editable-assign_driver-commission .info-html').html(commissionHtml);
            $('.editable-assign_driver-cash .info-html').html(cashHtml);
        },
        renderList: function() {
            this.$input = this.$tpl.find('input, select');

            this.formData = {
                driver: 0,
                vehicle: 0,
                total_price: 0,
                commission: 0,
                commission_calculated: 0,
                commission_percent: 0,
                cash: 0,
                cash_calculated: 0,
            };

            var that = this;
            var driver = that.$input.filter('[name="driver"]');
            var vehicle = that.$input.filter('[name="vehicle"]');
            var commission = that.$input.filter('[name="commission"]');
            var cash = that.$input.filter('[name="cash"]');
            var options = vehicle.closest('div.editable-assign_driver-options');

            that.fillItems(driver, that.sourceData);

            driver.change(function() {
                var driverId = parseInt($(this).val());
                that.getVehicleSource(driverId);

                $.each(that.sourceData, function(index, item) {
                    if (parseInt(item.value) == driverId) {
                        that.formData.commission_percent = parseFloat(item.commission).toFixed(2);
                        that.formData.commission_calculated = ((that.formData.total_price / 100) * that.formData.commission_percent).toFixed(2);
                        return false;
                    }
                });

                // if (that.formData.driver == 0) {
                    commission.val(that.formData.commission > 0 ? that.formData.commission : that.formData.commission_calculated);
                    cash.val(that.formData.cash > 0 ? that.formData.cash : that.formData.cash_calculated);
                // }

                if (driverId > 0) {
                    options.removeClass('hidden');
                }
                else {
                    options.addClass('hidden');
                }

                that.updateInfo();
            });

            commission.change(function() {
                that.updateInfo();
            });

            cash.change(function() {
                that.updateInfo();
            });

            this.$input.filter('[name="driver"], [name="vehicle"]').on('keydown.editable', function(e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        },
        // value2htmlFinal: function(value, element) {
        //
        // },
        // render: function() {
        //     this.$input = this.$tpl.find('input, select');
        // },
        // value2html: function(value, element) {
        //     if(!value) {
        //         $(element).empty();
        //         return;
        //     }
        //     var html = $('<div>').text(value.driver).html() + ', ' + $('<div>').text(value.commission).html() + ', ' + $('<div>').text(value.cash).html();
        //     $(element).html(html);
        // },
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
           if(!value) {
             return;
           }
           this.formData.driver = parseInt(value.driver);
           this.formData.vehicle = parseInt(value.vehicle);
           this.formData.total_price = parseFloat(value.total_price).toFixed(2);
           this.formData.commission = parseFloat(value.commission).toFixed(2);
           this.formData.cash = parseFloat(value.cash).toFixed(2);
           this.formData.cash_calculated = parseFloat(value.cash_calculated).toFixed(2);

           this.$input.filter('[name="driver"]').val(this.formData.driver).change();
           this.$input.filter('[name="vehicle"]').val(this.formData.vehicle);
           this.$input.filter('[name="commission"]').val(this.formData.commission).change();
           this.$input.filter('[name="cash"]').val(this.formData.cash).change();
           this.$input.filter('[name="notification"]').prop('checked', true);

           var commission = $('.editable-assign_driver-commission');
           var cash = $('.editable-assign_driver-cash');
           var account = $('.editable-assign_driver-account');

           var cashVal = this.formData.cash > 0 ? this.formData.cash : this.formData.cash_calculated;

           if (cashVal > 0) {
               cash.removeClass('hidden');
               account.addClass('hidden');
               account.html('');
           }
           else {
               cash.addClass('hidden');
               account.removeClass('hidden');
               account.html('<div style="margin:5px 0; font-size:12px;">Account Payment</div>');
           }
        },
        input2value: function() {
           return {
              driver: this.$input.filter('[name="driver"]').val(),
              vehicle: this.$input.filter('[name="vehicle"]').val(),
              commission: this.$input.filter('[name="commission"]').val(),
              cash: this.$input.filter('[name="cash"]').val(),
              notification: this.$input.filter('[name="notification"]').prop('checked') ? 1 : 0
           };
        },
        activate: function() {
            this.$input.filter('[name="driver"]').focus();
        },
        autosubmit: function() {
           this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
           });
        }
    });

    // <span class="title">Vehicle:</span>
    // <span class="title">Driver income:<span class="info-html"></span></span>
    // <span class="title">Cash to collect:<span class="info-html"></span></span>

    AssignDriver.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
        tpl: '<div class="editable-assign_driver"><select name="driver" class="form-control input-sm"><option value="0">Assign driver</option></select></div>'+
             '<div class="editable-assign_driver-options hidden">'+
                 '<div class="editable-assign_driver editable-assign_driver-vehicle"><select name="vehicle" class="form-control input-sm"><option value="0">Assign vehicle</option></select></div>'+
                 '<div class="editable-assign_driver editable-assign_driver-commission clearfix"><span class="title">Driver income:</span><input type="text" name="commission" value="" placeholder="0.00" class="form-control input-sm" style="width:70px; float:right;"></div>'+
                 '<div class="editable-assign_driver editable-assign_driver-cash clearfix"><span class="title">Cash to collect:</span><input type="text" name="cash" value="" placeholder="0.00" class="form-control input-sm" style="width:70px; float:right;"></div>'+
                 '<div class="editable-assign_driver editable-assign_driver-account"></div>'+
                 '<div class="editable-assign_driver hidden"><div class="checkbox"><label><input type="checkbox" name="notification" value="1"> Send notification</div></div>'+
             '<div>',
        inputclass: ''
    });

    $.fn.editabletypes.assign_driver = AssignDriver;

}(window.jQuery));
