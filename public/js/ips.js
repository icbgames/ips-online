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
    return values;
  };

  _.popupError = function(msg){
    q('.ips__error-popup-message').textContent = msg;
    q('#ips__error-popup').classList.add('show');
  };
  _.popupSuccess = function(msg){
    q('.ips__success-popup-message').textContent = msg;
    q('#ips__success-popup').classList.add('show');
  };
  
  _.popupEdit = function(channel, name, user, point) {
    var c = q('.ips__edit-popup-channel');
    var n = q('.ips__edit-popup-name');
    var u = q('.ips__edit-popup-user');
    var p = q('.ips__edit-popup-point');

    c.textContent = channel;
    n.textContent = name;
    u.textContent = user;
    p.value = point;

    q('.ips__edit-popup').classList.add('show');
  };

  _.closeEdit = function() {
    q('.ips__edit-popup').classList.remove('show');
  };

  _.overlay = function(on) {
    var b = q('body');
    var o = q('#ips__overlay');
    
    if(on) {
        b.classList.add('noscroll');
        o.classList.add('show');
    } else {
        b.classList.remove('noscroll');
        o.classList.remove('show');
    }
  };
}(this));


window.onload = function() {
  const q = (query) => document.querySelector(query);
  const qq = (query) => document.querySelectorAll(query);

  var __ = window.IPS;

  var submit = q('.ips__setting-submit');
  if(submit) {
      submit.addEventListener('click', function(e) {
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
          __.popupSuccess('正常に保存されました');
        }());
    });
  }

  var eclose = q('.ips__error-popup-close');
  if(eclose) {
    eclose.addEventListener('click', function(e) {
      q('#ips__error-popup').classList.remove('show');
    });
  }

  var sclose = q('.ips__success-popup-close');
  if(sclose) {
    sclose.addEventListener('click', function(e) {
      q('#ips__success-popup').classList.remove('show');
    });
  }

  var redit = qq('.ips__ranking-edit');
  if(redit) {
    redit.forEach(function(edit){
      edit.addEventListener('click', function(e) {
        var channel = this.getAttribute('x-ips-edit-channel');
        var name = this.getAttribute('x-ips-edit-name');
        var user = this.getAttribute('x-ips-edit-user');
        var point = this.getAttribute('x-ips-edit-point');
        __.overlay(true);
        __.popupEdit(channel, name, user, point);
      });
    });
  }

  var esubmit = q('.ips__edit-submit');
  if(esubmit) {
    esubmit.addEventListener('click', function(e) {
      var params = __.getEditValues();

      (async function() {
        const response = await fetch('/api/point', {
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
        __.popupSuccess('正常に更新されました');
      }());
    });
  }

  var ecancel = q('.ips__edit-cancel');
  if(ecancel) {
    ecancel.addEventListener('click', function(e) {
      __.closeEdit();
      __.overlay(false);
    });
  }
};
