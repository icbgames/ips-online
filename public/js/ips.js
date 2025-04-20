window.IPS = window.IPS || {};

(function(ips){
  const q = (query) => document.querySelector(query);
  const qq = (query) => document.querySelectorAll(query);

  var _ = window.IPS;

  _.getSettingValues = function(){
    var name        = q('input[name=ips-setting-name]').value;
    var unit        = q('input[name=ips-setting-unit]').value;
    var command     = q('input[name=ips-setting-command]').value;
    var period      = q('input[name=ips-setting-period]').value;
    var addition    = q('input[name=ips-setting-addition]').value;
    var addition_t1 = q('input[name=ips-setting-addition_t1]').value;
    var addition_t2 = q('input[name=ips-setting-addition_t2]').value;
    var addition_t3 = q('input[name=ips-setting-addition_t3]').value;

    var values = {
      name: name,
      unit: unit,
      command: command,
      period: period,
      addition: addition,
      addition_t1: addition_t1,
      addition_t2: addition_t2,
      addition_t3: addition_t3
    };
    console.log(values);
    return values;
  };

  _.popupError = function(msg){
    q('.ips__error-popup-message').textContent = msg;
    q('#ips__error-popup').classList.toggle('show');
  };
}(this));


window.onload = function() {
  const q = (query) => document.querySelector(query);
  const qq = (query) => document.querySelectorAll(query);

  var __ = window.IPS;

  q('.ips__setting-submit').addEventListener('click', function(e) {
    var params = __.getSettingValues();

    (async function() {
      const response = await fetch('/api/setting', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(params)
      });

      if(!response.ok) {
        const data = await response.json();
        __.popupError(data.message);
        return;
      }

      const data = await response.json();
      __.popupError('正常に保存されました');
    }());

  });

  q('.ips__error-popup-close').addEventListener('click', function(e) {
    q('#ips__error-popup').classList.toggle('show');
  });
};
