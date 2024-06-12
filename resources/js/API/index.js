import axios from "axios";
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

