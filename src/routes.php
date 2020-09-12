<?php
  $router = new Router();

  $router->get("/", ["HomeController","index"]);
  $router->post("/admin/login", ["SessionAdminController","store"]);
    
  $router->get("/util/jwt/validate", ["JwtController","validate"]);
  $router->get("/util/jwt/generate-key", ["JwtController","generateKey"]);
  $router->get("/util/jwt/generate-token", ["JwtController","generateToken"]);

  $router->use(["Auth","execute"]);

  $router->put("/admin/:id", ["AdminController", 'update']);
  $router->get("/admin", ["AdminController","index"]);
  $router->post("/admin", ["AdminController","store"]);

  // 404
  Response::getInstance()->status(404)->send("<h1>Página não econtrada</h1>");