<<<<<<< HEAD
import axios from "axios";
export const getOrders = () => {
  return fetch("https://dummyjson.com/carts/1").then((res) => res.json());
=======
import axios from 'axios';

// Fetch functions for different data types
export const getOrders = () => fetch("https://dummyjson.com/carts/1").then(res => res.json());
export const getRevenue = () => fetch("https://dummyjson.com/carts").then(res => res.json());
export const getInventory = () => fetch("https://dummyjson.com/products").then(res => res.json());
export const getCustomers = () => fetch("https://dummyjson.com/users").then(res => res.json());
export const getComments = () => fetch("https://dummyjson.com/comments").then(res => res.json());
export const getTasks = () => fetch("https://dummyjson.com/tasks").then(res => res.json()); // Corrected endpoint

// Fetch team tasks using async/await with error handling
export const getTeamstasks = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/tasks');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  } catch (error) {
    console.error('Error fetching Teamstask:', error);
    throw error;
  }
>>>>>>> 68e4740 (Issue -#35)
};
export const getCsrfToken = async () => {
  const response = await axios.get('http://127.0.0.1:8000/csrf-token');
  axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrfToken;
};


export const deleteTeamtask = async (deleteId) => {
  try {
    const response = await axios.delete(`http://127.0.0.1:8000/tasks/teams/${deleteId}`);
    return response.data;
  } catch (error) {
    // console.error('Error deleting Teamstask:', error);
    throw error;
  }
};


export const getTeamTaskById = async (id) => {
  try {
    const response = await fetch(`http://127.0.0.1:8000/tasks/teams/${id}`);
    if (!response.ok) {
      throw new Error("Failed to fetch team task");
    }
    return await response.json();
  } catch (error) {
    throw error;
  }
};
<<<<<<< HEAD
export const getCustomers = () => {
  return fetch("http://127.0.0.1:8000/customers")
    .then((res) => {
      if (!res.ok) {
        throw new Error("Network response was not ok");
      }
      return res.json();
    })
    .catch((error) => {
      console.error("Error fetching customers:", error);
      throw error;
    });
};

export const deleteCustomer = (customerId) => {
  const csrfTokenMetaTag = document.querySelector('meta[name="csrf-token"]');

  if (!csrfTokenMetaTag) {
    console.error("CSRF token meta tag not found");
    throw new Error("CSRF token meta tag not found");
  }

  const csrfToken = csrfTokenMetaTag.getAttribute('content');

  return fetch(`http://127.0.0.1:8000/customers/${customerId}`, {
    method: "DELETE",
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
  }).then((res) => {
    if (!res.ok) {
      throw new Error("Network response was not ok");
    }
    return res.json();
  });
};


export const updateCustomer = (customerId, newData) => {
  return fetch(`http://127.0.0.1:8000/customers/${customerId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(newData),
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      return res.json();
    })
    .catch((error) => {
      console.error('Error updating customer:', error);
      throw error;
    });
};

// API.js
export const getCustomerById = (customerId) => {
  return fetch(`http://127.0.0.1:8000/customers/${customerId}`)
    .then((res) => {
      if (!res.ok) {
        throw new Error("Network response was not ok");
      }
      return res.json();
    })
    .catch((error) => {
      console.error("Error fetching customer:", error);
      throw error;
    });
};






export const getComments = () => {
  return fetch("https://dummyjson.com/comments").then((res) => res.json());
=======

export const updateTeamTask = async (id, data) => {
  try {
    const response = await axios.put(`http://127.0.0.1:8000/tasks/teams/${id}`, data);
    return response.data;
  } catch (error) {
    console.error('API error:', error.response ? error.response.data : error.message);
    throw error;
  }
>>>>>>> 68e4740 (Issue -#35)
};

export const getCsrfToken = async () => {
  const response = await axios.get('http://127.0.0.1:8000/csrf-token');
  axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrfToken;
};

// Function to fetch goals (mocked data)
// const updatedTaskData = {
//   title: "Updated Task Title",
//   description: "Updated description",
//   // other task fields
// };

// updateTask(1, updatedTaskData)
//   .then(updatedTask => {
//     console.log('Task updated:', updatedTask);
//   })
//   .catch(error => {
//     console.error('Error updating task:', error);
//   });
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
          "invoiceDate": "2024-05-14",
          "dueDate": "2024-06-14",
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
          "invoiceDate": "2024-05-15",
          "dueDate": "2024-06-15",
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
          "invoiceDate": "2024-05-16",
          "dueDate": "2024-06-16",
        }
      ]
    };
    setTimeout(() => resolve(data), 1000); // Resolve the Promise with the JSON data after 1 second delay
  });
};
<<<<<<< HEAD

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

export const getProposals = () => {
  return new Promise((resolve, reject) => {
    const data = {
      "proposals": [
        {
          "date": "2024-06-14",
          "jobTitle": "International Usability Planner",
          "jobUrl": "https://www.sm.com/",
          "contactName": "John Doe",
          "author": "karlc",
          "status": "sent",
        },
        {
          "date": "2024-07-11",
          "jobTitle": "International Planner",
          "jobUrl": "https://www.smackcoders.com/",
          "contactName": "Jim Carrey",
          "author": "karlc",
          "status": "accepted",
        },
        {
          "date": "2024-06-14",
          "jobTitle": "International Usability Planner",
          "jobUrl": "https://www.bhurjkalifa.com/",
          "contactName": "Ash Melina",
          "author": "galce",
          "status": "sent",
        },
      ]
    };
    // Simulate an asynchronous operation with setTimeout
    setTimeout(() => {
      resolve(data); // Resolve the Promise with the JSON data
    }, 1000); // Simulate 1 second delay
  });
};

=======
>>>>>>> 68e4740 (Issue -#35)
