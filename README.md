# php-session-redis
###为什么要自己写一个SessionHandlerInterface的redis存储方案？
如果要使用redis来存储SESSION，最方便的方法是在php.ini中设置session.save_handler为redis。之所以要自己写，主要是为了解决空session填满redis的问题。

场景如下，假设一个浏览器不支持cookie，那么每访问一次网站中的页面（有session_start的页面）都会生成一个session_id，并且会将这个session_id保存到redis中，值是一个空的字符串，假设一个网站有几万的访问量，那么一天时间就会产生几十万的空session，占用大量的redis空间，而自己写的这个SessionHandlerInterface则可以解决这个问题，具体请看SessionHandlerInterface::write()方法

***需要注意的是，搜索引擎蜘蛛就相当于不支持cookie的浏览器

###使用方法<br>

$redis = new Redis();<br>
$redis->open("127.0.0.1");<br>

$session_handler = new Session_Redis($redis);<br>
session_set_save_handler($session_handler);<br>
/** 在session_start之前执行上面的代码 **/<br>
session_start();<br>
