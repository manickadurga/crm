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
  const [data, setData] = useState({
    customers: [],
});

  return (
    <DataContext.Provider value={{ data, setData }}>
      {children}
    </DataContext.Provider>
  );
};