  function printpage(aId) {
    var atext = document.getElementById(aId).innerHTML;
    var captext = window.document.title;
    var alink = window.document.location;
    var prwin = open('');
    prwin.document.open();
    prwin.document.writeln('<html><head><title>Версия для печати<\/title><style>body, a{color:#000000;}#container{margin:0 auto; width:1035px; }.product_left{margin-right:10px;width:685px;float:left;}.product_right{width:200px;float:left;}<\/style><\/head><body text="#000000" bgcolor="#ffffff"><div onselectstart="return false;" oncopy="return false;">');
    prwin.document.writeln('<div style="margin-bottom:5px;"><a href="javascript://" onclick="window.print();">Печать<\/a> • <a href="javascript://" onclick="window.close();">Закрыть окно<\/a><\/div><hr>');
    prwin.document.writeln('<h1>'+captext+'<\/h1>');
    prwin.document.writeln('<div id="container">'+atext+'<div style="clear:both;"><\/div><\/div>');
    prwin.document.writeln('<hr><div style="font-size:8pt;margin-top:20px;">©<\/div>');
    prwin.document.writeln('<div style="font-size:8pt;">Страница материала: '+alink+'<\/div>');
    prwin.document.writeln('<div style="margin-top:5px;"><a href="javascript://" onclick="window.print();">Печать<\/a> • <a href="javascript://" onclick="window.close();">Закрыть окно<\/a><\/div>');
    prwin.document.writeln('<\/div><\/body><\/html>');
  }
