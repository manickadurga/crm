import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  selectedTrigger: {
    triggerName: "",
    workflowName: ""
  },
};

const workFlowSlice = createSlice({
  name: "workFlow",
  initialState,
  reducers: {
    setSelectedTrigger(state, action) {
      state.selectedTrigger = action.payload; // Update selectedTrigger
    },
    resetSelectedTrigger(state) {
      state.selectedTrigger = { triggerName: "", workflowName: "" }; // Reset to default
    }
  },
});

export const { setSelectedTrigger, resetSelectedTrigger } = workFlowSlice.actions;
export default workFlowSlice.reducer;
