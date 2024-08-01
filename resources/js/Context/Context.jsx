// import React, { createContext, useState } from "react";

// export const DataContext = createContext();

// export function DataContextProvider({ children }) {
//   const [data, setData] = useState([
//     { id: '1', name: 'John' },
//     { id: '2', name: 'Jane' },
//     { id: '3', name: 'Alice' },
//     { id: '4', name: 'Bob' },
//     { id: '5', name: 'Charlie' }
//   ]);
//   return (
//     <DataContext.Provider value={{ data, setData }}>
//       {children}
//     </DataContext.Provider>
//   );
// }

// Context/Context.js


import React, { createContext, useState } from 'react';

// Create a context object
export const DataContext = createContext();

// Create a data provider component
export const DataProvider = ({ children }) => {
  const [data, setData] = useState([
    { id: 1, name: 'selvam' },
    { id: 2, name: 'selvam' },
    { id: 3, name: 'selvam' },
    { id: 4, name: 'selvam' },
  ]);
  const [FormFieldsState, setFormFieldsState] = useState([]);
  const [formData, setFormData] = useState([]);
  const [menuItem, setMenuItem] = useState([]);



  return (
    <DataContext.Provider value={{ 
      data,
      setData,
      FormFieldsState,
      setFormFieldsState,
      formData,
      setFormData,
      menuItem, 
      setMenuItem
    
    }}
      
      
      >
      {children}
    </DataContext.Provider>
  );
};



// import React, { createContext, useState } from 'react';

// // Create a context object
// export const DataContext = createContext();

// // Create a data provider component
// export const DataProvider = ({ children }) => {
//   const [data, setData] = useState([
//     {id:1,name:selvam},
//     {id:2,name:selvam},
//     {id:3,name:selvam},
//     {id:4,name:selvam},
//     // customers: [],
// ]);

//   return (
//     <DataContext.Provider value={{ data, setData }}>
//       {children}
//     </DataContext.Provider>
//   );
// };