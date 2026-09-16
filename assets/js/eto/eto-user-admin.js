

ETO.User.Admin = function() {
    var etoFn = {};

    etoFn.config = {
        init: [],
        lang: ['user'],
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'admin');
    };

    return etoFn;
}();
