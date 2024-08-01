import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { Col, Row, Button } from 'antd';

const Payment = () => {
  const { id } = useParams();
  const [invoice, setInvoice] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchInvoice = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/invoice/${id}`);
        setInvoice(response.data);
        setLoading(false);
      } catch (error) {
        setError('Failed to fetch invoice details');
        setLoading(false);
      }
    };

    fetchInvoice();
  }, [id]);

  const renderInvoiceDetails = (data) => {
    return Object.keys(data).map((key) => {
      if (Array.isArray(data[key])) {
        return (
          <div key={key}>
            <strong>{key.charAt(0).toUpperCase() + key.slice(1)}:</strong>
            <p>{data[key].join(', ')}</p>
          </div>
        );
      }

      if (typeof data[key] === 'object' && data[key] !== null) {
        return null;
      }

      return (
        <div key={key}>
          <strong>{key.charAt(0).toUpperCase() + key.slice(1)}:</strong>
          <p>{data[key] ? data[key].toString() : 'N/A'}</p>
        </div>
      );
    });
  };

  return (
    <div>
      <h1>Invoice Payment</h1>
      {loading ? (
        <p>Loading...</p>
      ) : error ? (
        <p>{error}</p>
      ) : (
        <Row>
          <Col lg={12} md={12}>
            {invoice && (
              <div>
                {renderInvoiceDetails(invoice)}
                <div style={{ marginTop: '10px' }}>
                  <Link to={`/invoices/edit/${id}`}>
                    <Button type="primary" style={{ marginRight: 10 }}>Edit</Button>
                  </Link>
                  <Link to="/invoices">
                    <Button>Back to Invoice</Button>
                  </Link>
                </div>
              </div>
            )}
          </Col>
        </Row>
      )}
    </div>
  );
};

export default Payment;
