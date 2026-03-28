(function(){
    function EmailJS() {
        this._publicKey = null;
    }

    EmailJS.prototype.init = function(publicKey) {
        this._publicKey = publicKey;
    };

    EmailJS.prototype.send = function(serviceId, templateId, templateParams) {
        var self = this;
        return new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://api.emailjs.com/api/v1.0/email/send', true);
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve(xhr.responseText);
                } else {
                    reject(new Error('EmailJS send failed: ' + xhr.status + ' ' + xhr.statusText + ' - ' + xhr.responseText));
                }
            };

            xhr.send(JSON.stringify({
                service_id: serviceId,
                template_id: templateId,
                user_id: self._publicKey,
                template_params: templateParams
            }));
        });
    };

    var emailjs = new EmailJS();
    window.emailjs = emailjs;
    window.EmailJS = emailjs;
})();