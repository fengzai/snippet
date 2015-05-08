#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Author: cloud@txthinking.com
# Date: 2014-01-10
#
# python 2.7 | easy_install/pip tornado ...
#
try:
    from tornado.ioloop import IOLoop
    from tornado import gen
    from tornado.httpserver import HTTPServer
    from tornado.web import Application
    from tornado.web import RequestHandler
    from tornado.websocket import WebSocketHandler
    from tornado.options import define, options
    import hiredis,redis,tornadoredis
except ImportError:
    print "[ERROR] $ sudo easy_install tornado hiredis redis tornadoredis"
    exit(1)

define("port", default=9191, type=int)
define("redishost", default="127.0.0.1", type=str)
define("redisport", default=6379, type=int)

class App(Application):
    def __init__(self):
        self._RP = redis.ConnectionPool(host=options.redishost, port=options.redisport, db=0, max_connections=1000)
        self._r = redis.StrictRedis(connection_pool=self._RP)
        Application.__init__(self, [(r"/", RootHandler), (r"/ws", WsHandler)], debug=True)

class RootHandler(RequestHandler):
    def get(self):
        html = """<!DOCTYPE html><html>
<head>
<meta charset="utf-8">
<style>
body{
    text-align:center;
}
</style>
</head>
<body>
    <div id="talk">
    </div>
    <div>
        <input id="speak" type="text">
    </div>
</body>
<script src="http://image.ledu.com/jslibs/modules/jquery.1.7.2.min.js"></script>
<script>
    websocket = new WebSocket("ws://"+location.host+"/ws");
    websocket.onopen = function(e){
        $("#talk").append("<p>[INFO] Connected</p>");
    }
    websocket.onclose = function(e) {
        $("#talk").append("<p>[INFO] Connection Closed</p>");
    }
    websocket.onmessage = function(e){
        for (;$("#talk p").length > 20;){
            $("#talk p").eq(0).remove();
        }
        $("#talk").append("<p>" + e.data + "</p>");
    }
    websocket.onerror = function(e) {
        $("#talk").append("<p>[ERROR] Connection Error</p>");
    }
    var sendMessage = function(v){
        if (v.replace(/(^\s*)|(\s*$)/g, "").length == 0){
            return
        }
        websocket.send(v);
    }
    $("#speak").keypress(function(ev){
        if (ev.keyCode === 13){
            sendMessage($(this).val())
        }
    });
</script>
</html>
"""
        self.set_header("Content-Type", "text/html")
        self.write(html)

class WsHandler(WebSocketHandler):
    def open(self):
        self._r = self.application._r
        self._ar = tornadoredis.Client(host=options.redishost, port=options.redisport)
        self._ar.connect()
        if self.request.headers.has_key("X-You-Do-Not-Know-The-Fucking-Ip"):
            self.request.remote_ip = self.request.headers["X-You-Do-Not-Know-The-Fucking-Ip"]
        self.bc()
        print "[JOIN]: " + self.request.remote_ip

    @gen.engine
    def bc(self):
        yield gen.Task(self._ar.subscribe, 'message')
        self._ar.listen(self.send)

    def send(self, msg):
        if msg.kind == "message":
            self.write_message(msg.body)
        elif msg.kind == "subscribe":
            ol = self._r.execute_command("pubsub", "numsub", "message")
            self._r.publish("message", "[INFO] Online users count: " + str(ol[1]))
        elif msg.kind == "unsubscribe" or msg.kind == 'disconnect':
            if self._ar.connection.connected():
                self._ar.disconnect()
            ol = self._r.execute_command("pubsub", "numsub", "message")
            self._r.publish("message", "[INFO] Online users count: " + str(ol[1]))
        print msg

    def on_message(self, message):
        m = "[" + self.request.remote_ip + "]: " + message.encode("utf-8")
        self._r.publish("message", m)

    def on_close(self):
        if self._ar.subscribed:
            self._ar.unsubscribe('message')
        print "[LEAVE]: " + self.request.remote_ip

def main():
    options.parse_command_line()
    server = HTTPServer(App())
    server.listen(options.port)
    iol = IOLoop.instance()
    iol.start()

if __name__ == "__main__":
    main()

