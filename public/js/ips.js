window.IPS = window.IPS || {};

(function(ips){
  if(!authResult.accessToken || !authResult.refreshToken || !authResult.login || !authResult.expire || !authResult.signature) {
    return;
  }
  console.log('Access Token: ' + authResult.accessToken);
  console.log('Refresh Token: ' + authResult.refreshToken);
  console.log('login: ' + authResult.login);
  consolo.log('expire: ' + authResult.expire);
  console.log('signature: ' + authResult.signature);

  var data = {
    a: authResult.accessToken,
    r: authResult.refreshToken,
    l: authResult.login,
    e: authResult.expire,
    s: authResult.signature
  };
  console.log(data);

  var j = JSON.stringify(data);
  console.log(j);

  window.localStorage.setItem('ips-user-info', j);
}(this));
