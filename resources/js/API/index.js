
// import axios from 'axios'; 

export const getOrders = () => {
  return fetch("https://dummyjson.com/carts/1").then((res) => res.json());
};

export const getRevenue = () => {
  return fetch("https://dummyjson.com/carts").then((res) => res.json());
};

export const getInventory = () => {
  return fetch("https://dummyjson.com/products").then((res) => res.json());
};
export const getTasks = () => {
  return fetch("https://dummyjson.com/task").then((res) => res.json());
};


export const getCustomers = () => {
  return fetch("https://dummyjson.com/customers").then((res) => res.json());
};
// console.log(getCustomers());

export const getComments = () => {
  return fetch("https://dummyjson.com/comments").then((res) => res.json());
};

  export const getInvoices = async () => {
    try {
      const response = await fetch('http://127.0.01:8000/invoice'); // Update this URL to the correct API endpoint
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      // console.log(data.teamtasks);

      return data;
    } catch (error) {
      console.error('Error fetching Invoices:', error);
      throw error; // Re-throw the error so it can be caught by the caller
    }
  };

  import axios from 'axios';

  export const deleteInvoice = async (deleteId) => {
    try {
      const response = await axios.delete(`http://127.0.0.1:8000/invoices/${deleteId}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting invoice:', error);
      throw error; // Re-throw the error so it can be caught by the caller
    }
  };


  export const getInvoicesId = async (id) => {
      const response = await axios.get(`http://127.0.0.1:8000/invoices/${id}`);
      return response.data;
  };

  export const updateInvoices = async (id) => {
    try {
      const response = await axios.put(`http://127.0.0.1:8000/invoices/${id}`,data);
      return response.data;
    } catch (error) {
      console.error('Error deleting invoice:', error);
      throw error; // Re-throw the error so it can be caught by the caller
    }
  };
  

  