import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { Col, Image, Row, Tabs, Collapse, Button } from 'antd';
import ViewMap from '../../Components/ViewMap';
function CustomerView() {
  const { id } = useParams();
  const [customer, setCustomer] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchCustomer = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/customers/${id}`);
        setCustomer(response.data.customer);
        setLoading(false);
      } catch (error) {
        setError('Failed to fetch customer details');
        setLoading(false);
      }
    };

    fetchCustomer();
  }, [id]);

  const renderCustomerDetails = (data) => {
    return Object.keys(data).map((key) => {
      if (typeof data[key] === 'object') {
        return null;
      }
      return (
        <div key={key}>
          <strong>{key.charAt(0).toUpperCase() + key.slice(1)}:</strong>
          <p>{data[key]}</p>
        </div>
      );
    });
  };

  const tabItems = [
    {
      key: '1',
      label: 'Details',
      children:(
        <>
        {customer && renderCustomerDetails(customer)}
        {customer && customer.location && (
            <div style={{ marginTop: '20px' }}>
              <strong>Location:</strong>
              <ViewMap/>
            </div>
          )}
        </>
        )
    },
    {
      key: '2',
      label: 'Members',
      children: "Member Page ready soon.",
    },
  ];

  const collapseItems = [
    {
      key: '1',
      label: 'About',
      children: customer && renderCustomerDetails(customer),
    },
    {
      key: '2',
      label: 'Projects',
      children: <p>Projects content goes here.</p>,
    },
    {
      key: '3',
      label: 'Members',
      children: <p>Members content goes here.</p>,
    },
  ];

  if (loading) {
    return <div>Loading...</div>;
  }

  if (error) {
    return <div>{error}</div>;
  }

  return (
    <div>
      <h1>Customer Details</h1>
      <Row>
        <Col lg={12} md={12}>
          {customer && (
            <div>
              <Col lg={10}>
                <Collapse accordion items={collapseItems} />
              </Col>
              {customer.image && (
                <div>
                  <strong>Image:</strong>
                  <Image
                    width={200}
                    src={`http://127.0.0.1:8000/storage/${customer.image}`}
                    alt="Customer"
                    style={{ borderRadius: '10px' }}
                  />
                </div>
              )}
              <div style={{ marginRight: '10px' }}>
                <Link to={`/customers/edit/${id}`}>
                  <Button type="primary" style={{ marginRight: 10 }}>Edit</Button>
                </Link>
                <Link to="/customers">
                  <Button>Back to Customers</Button>
                </Link>
              </div>
            </div>
          )}
        </Col>
        <Col lg={12} md={12}>
          <Tabs defaultActiveKey="1" items={tabItems} />
        </Col>
      </Row>
    </div>
  );
}

export default CustomerView;





