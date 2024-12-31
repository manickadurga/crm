// import React from "react";
// import ReactDOM from "react-dom/client";


// // import reportWebVitals from "./reportWebVitals";
// import { BrowserRouter } from "react-router-dom";
// import SideMenu from "./Components/SideMenu";
// import PageContent from "./Components/PageContent";
// import "../css/common.css";

// import "./App.css";

// function App() {
//   return (
//     <div className="App">
//       {/* <AppHeader /> */}
//       {/* <div className="SideMenuAndPageContent"> */}
//         {/* <SideMenu/> */}
//         <PageContent/>
//       {/* </div> */}
//       {/* <AppFooter /> */}
//     </div>
//   );
// }
// const root = ReactDOM.createRoot(document.getElementById("root"));
// root.render(
//   <React.StrictMode>
//     <BrowserRouter>
//       <App />
//     </BrowserRouter>
//   </React.StrictMode>
// );
// // reportWebVitals();
// // import React from "react";
// // import { createRoot } from "react-dom/client";
// // import { createBrowserRouter, RouterProvider, Route, Routes } from "react-router-dom";

// // // import routes from "./routes";
// // const routes = [
// //   {
// //     path: "/",
// //     element: <div>Home</div>
// //   }
// // ];
// // // import SideMenu from "./pages/home/menu";

// // const router = createBrowserRouter(routes);
// // console.log(routes)
          
// // const App = () => {
// //   return (
// //     <div style={{minHeight:"100vh"}}>app.js
// //         {/* <SideMenu /> */}
// //     {/* <Content /> */}
// //      <RouterProvider router={router} />
// //     </div>
// //   );
// // };

// // createRoot(document.getElementById("root")).render(<App />);


import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import PageContent from "./Components/PageContent";
import "../css/common.css";
import "./App.css";
import {Provider} from "react-redux"
import store from "./store/store";

function App() {
  return (
    <div className="App">
      <PageContent/>
    </div>
  );
}

const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(
  <React.StrictMode>
   <BrowserRouter>
    <Provider store={store}>
      <App />
    </Provider> 
  </BrowserRouter>
 </React.StrictMode>
);
