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
    var raid        = q('input[name=ips-setting-raid]').value;
    var raid_bonus  = q('input[name=ips-setting-raid_bonus]').value;
    var bits100     = q('input[name=ips-setting-bits100]').value;
    var gift_t1     = q('input[name=ips-setting-gift_t1]').value;
    var gift_t2     = q('input[name=ips-setting-gift_t2]').value;
    var gift_t3     = q('input[name=ips-setting-gift_t3]').value;
    var setting_acl_input = document.querySelector('input[name="ips-setting-setting-acl"]:checked');
    var setting_acl = setting_acl_input ? parseInt(setting_acl_input.value, 10) : 0;
    var ranking_acl_input = document.querySelector('input[name="ips-setting-ranking-acl"]:checked');
    var ranking_acl = ranking_acl_input ? parseInt(ranking_acl_input.value, 10) : 0;

    var values = {
      name: name,
      unit: unit,
      command: command,
      period: period,
      addition: addition,
      addition_t1: addition_t1,
      addition_t2: addition_t2,
      addition_t3: addition_t3,
      raid: raid,
      raid_bonus: raid_bonus,
      bits100: bits100,
      gift_t1: gift_t1,
      gift_t2: gift_t2,
      gift_t3: gift_t3
      , setting_acl: setting_acl
      , ranking_acl: ranking_acl
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

  _.getEditValues = function() {
    var channel = q('.ips__edit-popup-channel').textContent;
    var user = q('.ips__edit-popup-user').textContent;
    var point = q('.ips__edit-popup-point').value;

    var values = {
      channel: channel,
      user: user,
      point: point
    };

    return values;
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
        __.popupSuccess('正常に更新されました (2秒後にリロードします)');
        setTimeout(function(){ location.reload(); }, 2100);
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

  // Ignore page: add/delete via AJAX
  var ignoreAddBtn = q('.ips__ignore-add');
  if(ignoreAddBtn) {
    ignoreAddBtn.addEventListener('click', function(e){
      var form = document.querySelector('.ips__ignore-form');
      var login = form.querySelector('input[name=login]').value.trim();
      if(!login) return;
      var channel = document.querySelector('.ips__login-name strong') ? document.querySelector('.ips__login-name strong').textContent : '';
      fetch('/api/ignore', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ channel: channel, user: login })
      }).then(function(res){
        return res.json().then(function(body){ return {status: res.status, body: body}; });
      }).then(function(r){
        if(r.status === 200) {
            var ul = document.querySelector('.ips__ignore-list-ul');
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '#'; a.className = 'ips__ignore-delete'; a.setAttribute('data-login', login); a.textContent = login;
            a.addEventListener('click', function(ev){ ev.preventDefault(); onClickDelete(a, li); });
            li.appendChild(a);
            ul.appendChild(li);
          form.querySelector('input[name=login]').value = '';
          __.popupSuccess('追加しました: ' + login);
        } else {
          __.popupError(r.body.message || '追加に失敗しました');
        }
      }).catch(function(){ __.popupError('通信エラー'); });
    });
  }

  function deleteIgnoreUser(login, row) {
    var channel = document.querySelector('.ips__login-name strong') ? document.querySelector('.ips__login-name strong').textContent : '';
    return fetch('/api/ignore', {
      method: 'DELETE',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ channel: channel, user: login })
    }).then(function(res){
      return res.json().then(function(body){ return {status: res.status, body: body}; });
    }).then(function(r){
      if(r.status === 200) {
        if(row && row.parentNode) row.parentNode.removeChild(row);
        __.popupSuccess('削除しました: ' + login);
        return true;
      } else {
        __.popupError(r.body.message || '削除に失敗しました');
        return false;
      }
    }).catch(function(){ __.popupError('通信エラー'); return false; });
  }

  function onClickDelete(anchor, li) {
    var login = anchor.getAttribute('data-login');
    var confirmed = window.confirm(login + ' を対象外ユーザーから削除してもよろしいですか？');
    if(!confirmed) return;
    deleteIgnoreUser(login, li);
  }

  // wire up existing delete buttons
  qq('.ips__ignore-delete').forEach(function(el){
    el.addEventListener('click', function(ev){
      ev.preventDefault();
      var login = el.getAttribute('data-login');
      var li = el.closest('li');
      onClickDelete(el, li);
    });
  });

  // Channelpoint: add row to register list
  var cpAddBtn = q('.ips__channelpoint-add-row');
  if(cpAddBtn) {
    cpAddBtn.addEventListener('click', function(){
      var ul = q('.ips__channelpoint-register-list');
      if(!ul) return;
      var firstLi = ul.querySelector('li');
      if(!firstLi) return;
      var clone = firstLi.cloneNode(true);
      // clear input values inside clone
      var inputs = clone.querySelectorAll('input');
      inputs.forEach(function(inp){ inp.value = ''; });
      ul.appendChild(clone);
    });
  }

  // Channelpoint: register handler
  var cpRegisterBtn = q('.ips__channelpoint-register');
  if(cpRegisterBtn) {
    cpRegisterBtn.addEventListener('click', function(){
      // find selected reward id
      var selected = q('input[name="channelpoint-target"]:checked');
      if(!selected) { __.popupError('対象のチャンネルポイント報酬を1つ選択してください'); return; }
      var id = selected.value;

      var ul = q('.ips__channelpoint-register-list');
      if(!ul) { __.popupError('登録フォームが見つかりません'); return; }

      var rows = ul.querySelectorAll('li');
      if(!rows || rows.length === 0) { __.popupError('登録する行がありません'); return; }

      var data = [];
      var totalPermillage = 0;
      for(var i=0;i<rows.length;i++){
        var li = rows[i];
        var msgInput = li.querySelector('input[name="message[]"]');
        var probInput = li.querySelector('input[name="probability[]"]');
        var pointInput = li.querySelector('input[name="point[]"]');

        var message = msgInput ? msgInput.value.trim() : '';
        if(!message || message.length === 0 || message.length > 30) { __.popupError('メッセージは1文字以上30文字以内で入力してください'); return; }

        var probStr = probInput ? probInput.value.trim() : '';
        probStr = probStr.replace(',', '.');
        var prob = parseFloat(probStr);
        if(!(isFinite(prob))) { __.popupError('確率には数値を入力してください'); return; }
        if(prob < 0.1 || prob > 100) { __.popupError('確率は0.1以上100以下で指定してください'); return; }
        // check 0.1 multiple: multiply by 10 and check integer
        var permillage = Math.round(prob * 10);
        if(Math.abs(prob * 10 - permillage) > 1e-6) { __.popupError('確率は0.1の倍数で指定してください (例: 0.1, 1.0, 12.3)'); return; }
        if(permillage < 1 || permillage > 1000) { __.popupError('確率の値が不正です'); return; }

        var point = pointInput ? parseInt(pointInput.value, 10) : NaN;
        if(!isFinite(point) || isNaN(point) || point < 0 || point > 999999) { __.popupError('付与ポイントは0以上999999以下の整数で指定してください'); return; }

        totalPermillage += permillage;
        data.push({ message: message, permillage: permillage, point: point });
      }

      if(totalPermillage !== 1000) {
        __.popupError('確率の合計は100%（合計で100.0）になる必要があります');
        return;
      }

      // send request
      cpRegisterBtn.disabled = true;
      fetch('/api/channelpoint', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: id, data: data })
      }).then(function(res){
        return res.json().then(function(body){ return { status: res.status, body: body }; });
      }).then(function(r){
        cpRegisterBtn.disabled = false;
        if(r.status === 200) {
          __.popupSuccess('正常に登録されました');
        } else {
          __.popupError(r.body && r.body.message ? r.body.message : '登録に失敗しました');
        }
      }).catch(function(){ cpRegisterBtn.disabled = false; __.popupError('通信エラー'); });
    });
  }

  // Channelpoint: registered trigger delete
  var regDeleteBtns = qq('.ips__registered-trigger-delete');
  if(regDeleteBtns) {
    regDeleteBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = btn.getAttribute('x-ips-channel-point-id');
        if(!id) return;
        var confirmed = window.confirm('このトリガーを削除してもよろしいですか？');
        if(!confirmed) return;

        fetch('/api/channelpoint', {
          method: 'DELETE',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ id: id })
        }).then(function(res){
          return res.json().then(function(body){ return { status: res.status, body: body }; });
        }).then(function(r){
          if(r.status === 200) {
            __.popupSuccess('削除されました');
            setTimeout(function(){ location.reload(); }, 700);
          } else if(r.status === 403) {
            __.popupError('権限がありません');
          } else if(r.status === 404) {
            __.popupError('該当データが見つかりませんでした');
          } else {
            __.popupError(r.body && r.body.message ? r.body.message : '削除に失敗しました');
          }
        }).catch(function(){ __.popupError('通信エラー'); });
      });
    });
  }

  // Ranking: reset all points (owner only)
  var resetBtn = q('#ips__reset-button');
  if(resetBtn) {
    resetBtn.addEventListener('click', function(){
      var input = q('#ips__reset-account');
      var entered = input ? input.value.trim() : '';
      var params = new URLSearchParams(window.location.search);
      var displayedChannel = params.has('channel') ? params.get('channel') : (document.getElementById('ips__template-channel') ? document.getElementById('ips__template-channel').value : '');
      var operator = document.getElementById('ips__operator-login') ? document.getElementById('ips__operator-login').value : '';

      if(!entered) { __.popupError('アカウントを入力してください'); return; }
      if(displayedChannel !== operator || operator !== entered) {
        __.popupError('入力されたTwitchアカウントが正しくありません');
        return;
      }

      var confirmed = window.confirm('この操作は取り消せません。本当に全リセットしますか？');
      if(!confirmed) return;

      fetch('/api/reset', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ channel: displayedChannel })
      }).then(function(res){
        return res.json().then(function(body){ return {status: res.status, body: body}; });
      }).then(function(r){
        if(r.status === 200) {
          __.popupSuccess('すべてのポイントを削除しました');
          setTimeout(function(){ location.reload(); }, 800);
        } else {
          __.popupError(r.body.message || 'リセットに失敗しました');
        }
      }).catch(function(){ __.popupError('通信エラー'); });
    });
  }
};
