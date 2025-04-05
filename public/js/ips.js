window.IPS = window.IPS || {};

(function(ips){
  if(!accessToken || !refreshToken || !login || !expire || !signature) {
    return;
  }
  console.log('Access Token: ' + accessToken);
  console.log('Refresh Token: ' + refreshToken);
  console.log('login: ' + login);
  consolo.log('expire: ' + expire);
  console.log('signature: ' + signature);

  var data = {
    a: accessToken,
    r: refreshToken,
    l: login,
    e: expire,
    s: signature
  };
  console.log(data);

  var j = JSON.stringify(data);
  console.log(j);

  window.localStorage.setItem('ips-user-info', j);
}(this));
