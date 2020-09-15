<?php
  $router = new Router();

  $router->get("/", ["HomeController","index"]);
  $router->post("/admin/login", ["SessionAdminController","store"]);
    
  $router->get("/util/jwt/generate-key", ["JwtController","generateKey"]);
  $router->get("/util/jwt/generate-token", ["JwtController","generateToken"]);
  $router->get("/util/jwt/validate", ["JwtController","validate"]);

  $router->use(["Auth","execute"]);

  $router->put("/admin/password", ["AdminPasswordController","update"]);

  $router->get("/admin", ["AdminController","index"]);
  $router->get("/admin/:id", ["AdminController","view"]);
  $router->post("/admin", ["AdminController","store"]);
  $router->put("/admin/:id", ["AdminController", 'update']);
  $router->delete("/admin/:id", ["AdminController","destroy"]);
  

  // 404
  Response::getInstance()->status(404)->send("<h1>Página não econtrada</h1>");