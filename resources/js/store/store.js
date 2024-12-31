import { configureStore } from "@reduxjs/toolkit";
import workFlowReducer from "./workFlowSlice"; // Import the workFlowSlice reducer

// Create a Redux store
const store = configureStore({
  reducer: {
    workFlow: workFlowReducer, // Register the workFlow slice in the store
  },
});

export default store;
