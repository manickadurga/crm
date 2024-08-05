import axios from "axios";

export const getInvoices = (page) => {
    return axios.get(`http://127.0.0.1:8001/invoice?page=${page}`)
      .then((response) => {
        console.log("Fetched formfields:", response.data);
      
        return response.data; // Ensure the data is still returned for further processing
      })
      .catch((error) => {
        console.error("Error fetching formfields:", error);
        throw error;
      });
  };
  export const getInvoicesById = async (invoiceId) => {
    try {
      console.log("jdjdhfdhfjddfdj",invoiceId)
      const response = await axios.get(`http://127.0.0.1:8000/invoice/${invoiceId}`);
      return response.data; // Assuming your server returns JSON data for the customer
    } catch (error) {
      console.error('Error fetching customer:', error);
      throw error; // Re-throw the error for the caller to handle
    }
  };

export const getFormfieldsInVoices = () => {
  return axios.get("http://127.0.0.1:8000/form-fields?name=Invoices")
    .then((response) => {
      console.log("Fetched formfields:", response.data);
    
      return response.data; // Ensure the data is still returned for further processing
    })
    .catch((error) => {
      console.error("Error fetching formfields:", error);
      throw error;
    });
};


