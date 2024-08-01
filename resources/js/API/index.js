import axios from 'axios';

export const getMenu = async (page) => {
  try {
    const response = await axios.get(`http://127.0.0.1:8001/menuitems`);
    console.log("Fetched menu:", response.data);
    return response.data;
  } catch (error) {
    console.error("Error fetching menu:", error);
    throw error;
  }
};
export const getOrders = () => {
  return fetch("https://dummyjson.com/carts/1").then((res) => res.json());
};
export const getCsrfToken = async () => {
  const response = await axios.get('http://127.0.0.1:8000/csrf-token');
  axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrfToken;
};

export const getRevenue = () => {
  return fetch("https://dummyjson.com/carts").then((res) => res.json());
};

export const getInventory = () => {
  return fetch("https://dummyjson.com/products").then((res) => res.json());
};

// http://127.0.0.1:8000/form-fields

export const getFormfields = (form) => {
  return axios.get(`http://127.0.0.1:8001/formfields?name=${form}`)
    .then((response) => {
      console.log("Fetched formfields:", response.data);
    
      return response.data; // Ensure the data is still returned for further processing
    })
    .catch((error) => {
      console.error("Error fetching formfields:", error);
      throw error;
    });
};


export const getDataFunction = async (pathName , page) => {
  try {
    const response = await axios.get(`http://127.0.0.1:8001/api/${pathName}?page=${page}`);
    console.log("Fetched data:", response.data);
    return response.data;
  } catch (error) {
    console.error("Error fetching Data:", error);
    throw error;
  }
};

// Example usage in a React component or module
// const baseURL = REACT_APP_BASE_URL;

// console.log(baseURL); // This will log "http://127.0.0.1:8000"


export const getClient = async (page) => {
  try {
    const response = await axios.get(`http://127.0.0.1:8000/clients?page=${page}`);
    console.log("Fetched client:", response.data);
    return response.data;
  } catch (error) {
    console.error("Error fetching client:", error);
    throw error;
  }
};

export const getClientById = async (clientId) => {
  try {
    console.log("jdjdhfdhfjddfdj",clientId)
    const response = await axios.get(`http://127.0.0.1:8000/clients/${clientId}`);
    return response.data; // Assuming your server returns JSON data for the customer
  } catch (error) {
    console.error('Error fetching customer:', error);
    throw error; // Re-throw the error for the caller to handle
  }
};

export const getLeadById = async (clientId) => {
  try {
    console.log("jdjdhfdhfjddfdj",clientId)
    const response = await axios.get(`http://127.0.0.1:8000/leads/${clientId}`);
    return response.data; // Assuming your server returns JSON data for the customer
  } catch (error) {
    console.error('Error fetching customer:', error);
    throw error; // Re-throw the error for the caller to handle
  }
};

export const getCustomerById = async (customerId) => {
  try {
    // console.log("jdjdhfdhfjddfdj",customerId)
    const response = await axios.get(`http://127.0.0.1:8000/customers/${customerId}`);
    return response.data; // Assuming your server returns JSON data for the customer
  } catch (error) {
    console.error('Error fetching customer:', error);
    throw error; // Re-throw the error for the caller to handle
  }
};

export const deleteItem = async (deletepath,deleteId) => {
  try {
    const csrfTokenMetaTag = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMetaTag) {
      throw new Error("CSRF token meta tag not found");
    }
    const csrfToken = csrfTokenMetaTag.getAttribute('content');

    const response = await axios.delete(`http://127.0.0.1:8000/${deletepath}/${deleteId}`, {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });

    return response.data; // Assuming you expect JSON response on success
  } catch (error) {
    console.error('Error deleting customer:', error);
    throw error;
  }
};



export const getComments = () => {
  return fetch("https://dummyjson.com/comments").then((res) => res.json());
};

export const getGoals = () => {
  return new Promise((resolve, reject) => {
    const data = {
      "invoices": [
        {
          "invoiceNumber": "INV-001",
          "invoiceDate": "2024-05-14",
          "dueDate": "2024-06-14",
          "totalValue": 1000.00,
          "tax": 50.00,
          "tax2": 20.00,
          "discount": 100.00,
          "contact": "John Doe",
          "tags": ["urgent", "important"],
          "paidStatus": "Paid",
          "status": "Completed",
        },
        {
          "invoiceNumber": "INV-002",
          "invoiceDate": "2024-05-15",
          "dueDate": "2024-06-15",
          "totalValue": 1500.00,
          "tax": 75.00,
          "tax2": 30.00,
          "discount": 50.00,
          "contact": "Jane Smith",
          "tags": ["pending"],
          "paidStatus": "Unpaid",
          "status": "Pending",
        },
        {
          "invoiceNumber": "INV-003",
          "invoiceDate": "2024-05-16",
          "dueDate": "2024-06-16",
          "totalValue": 2000.00,
          "tax": 100.00,
          "tax2": 40.00,
          "discount": 200.00,
          "contact": "Michael Johnson",
          "tags": ["completed", "paid"],
          "paidStatus": "Paid",
          "status": "Completed",
        }
      ]
    };
    // Simulate an asynchronous operation with setTimeout
    setTimeout(() => {
      resolve(data); // Resolve the Promise with the JSON data
    }, 1000); // Simulate 1 second delay
  });
};

export const getProposals = () => {
  return new Promise((resolve, reject) => {
    const data = {
      "proposals": [
        {
          "id":"1",
          "name": "lucas",
          "Description": "International Usability Planner",
          "stage": "John Doe",
          "status": "sent",
        },
        {
          "id":"2",
          "date": "2024-07-11",
          "Description": "International Planner",
          "stage": "John Doe",
          "status": "accepted",
        },
        {
          "id":"3",
          "date": "2024-06-14",
          "Description": "International Usability Planner",
          "stage": "John Doe",
          "status": "sent",
        },
      ]
    };

    // Resolve the Promise with the data
    setTimeout(() => {
      resolve(data);
    }, 1000); // Simulating a delay of 1 second
  });
};


export const getTasks = () => {
  return new Promise((resolve, reject) => {
    const data = {
      "tasks": [
        {
          "id": "#KAR-2",
          "title": "[Feature] Add time range selector values into URL for all pages / reports where possible",
          "project": "Gauzy Web Site",
          "members": 13,
          "createdBy": "karlc",
          "createdAt": "2024-06-14",
          "dueDate": "2024-06-14",
          "employeeTeams": ["John Doe", "Rusian"],
          "tags": ["urgent", "important"],
          "status": "open",
        },
        {
          "id": "#GAU-3",
          "title": "[Feature] Time range selector add values into URL for all pages / reports where possible",
          "project": "Gauzy Web Site",
          "members": 13,
          "createdBy": "gaulc",
          "createdAt": "2024-06-11",
          "dueDate": "2024-06-14",
          "employeeTeams": ["John Doe", "Rusian"],
          "tags": ["completed"],
          "status": "inprogress"
        },
        {
          "id": "#REV-3",
          "title": "[Feature] Add time range selector values into URL for all pages / reports where possible",
          "project": "Gauzy Web Site",
          "members": 13,
          "createdBy": "Revcld",
          "createdAt": "2024-06-11",
          "dueDate": "2024-06-14",
          "employeeTeams": ["John Doe", "Rusian"],
          "tags": ["paid", "important"],
          "status": "closed"
        },
      ]
    };
    // Simulate an asynchronous operation with setTimeout
    setTimeout(() => {
      resolve(data); // Resolve the Promise with the JSON data
    }, 1000); // Simulate 1 second delay
  });
};

