import React from "react";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import routes from "./routes";
import MenuItem from "antd/es/menu/MenuItem";

function AppRoutes() {
  return (
    <Routes>
      {routes.map((route, index) => (
        <Route
          key={index}
          path={route.path}
          children={route.children}
          element={route.element}
        />
      ))}
    </Routes>
  );
}
export default AppRoutes;
