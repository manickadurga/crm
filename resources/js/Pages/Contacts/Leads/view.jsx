import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { Col, Image, Row, Tabs, Collapse, Button } from 'antd';
// import ViewMap from '../../Components/ViewMap';
import ViewMap from '../../../Components/ViewMap'
function LeadView() {
  const { id } = useParams();
  const [lead, setLead] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {

    const fetchLead = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/leads/${id}`);
        setLead(response.data.lead);
        setLoading(false);
      } catch (error) {
        setError('Failed to fetch lead details');
        setLoading(false);
      }
    };

    fetchLead();
  }, [id]);

  const renderLeadDetails = (data) => {
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
        {lead && renderLeadDetails(lead)}
        {lead && lead.location && (
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
      children: lead && renderLeadDetails(lead),
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
      <h1>Lead Details</h1>
      <Row>
        <Col lg={12} md={12}>
          {lead && (
            <div>
              <Col lg={10}>
                <Collapse accordion items={collapseItems} />
              </Col>
              {lead.image && (
                <div>
                  <strong>Image:</strong>
                  <Image
                    width={200}
                    src={`http://127.0.0.1:8000/storage/${lead.image}`}
                    alt="lead"
                    style={{ borderRadius: '10px' }}
                  />
                </div>
              )}
              <div style={{ marginRight: '10px' }}>
                <Link to={`/leads/edit/${id}`}>
                  <Button type="primary" style={{ marginRight: 10 }}>Edit</Button>
                </Link>
                <Link to="/leads">
                  <Button>Back to Leads</Button>
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

export default LeadView;





