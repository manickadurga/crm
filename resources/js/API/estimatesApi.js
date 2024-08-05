import axios from "axios";

export const getEstimates = (page) => {
    return axios.get(`http://127.0.0.1:8000/estimates?page=${page}`)
      .then((response) => {
        console.log("Fetched formfields:", response.data);
      
        return response.data; // Ensure the data is still returned for further processing
      })
      .catch((error) => {
        console.error("Error fetching formfields:", error);
        throw error;
      });
  };
  export const getEstimatesById = async (estimateId) => {
    try {
      console.log("jdjdhfdhfjddfdj",estimateId)
      const response = await axios.get(`http://127.0.0.1:8000/estimates/${estimateId}`);
      return response.data; // Assuming your server returns JSON data for the customer
    } catch (error) {
      console.error('Error fetching customer:', error);
      throw error; // Re-throw the error for the caller to handle
    }
  };

export const getFormfieldsEstimates = () => {
  return axios.get("http://127.0.0.1:8000/form-fields?name=Estimates")
    .then((response) => {
      console.log("Fetched formfields:", response.data);
    
      return response.data; // Ensure the data is still returned for further processing
    })
    .catch((error) => {
      console.error("Error fetching formfields:", error);
      throw error;
    });
};


