import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { Col, Image, Row, Tabs, Collapse, Button } from 'antd';
import ViewMap from '../../../Components/ViewMap';

const { TabPane } = Tabs;
const { Panel } = Collapse;

function ClientView() {
  const { id } = useParams();
  const [client, setClient] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchClient = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/jo-clients/${id}`);
        console.log('clientview',response.data)
         setClient(response.data);
        setLoading(false);
      } catch (error) {
        setError('Failed to fetch client details');
        setLoading(false);
      }
    };

    fetchClient();
  }, [id]);

  const renderClientDetails = (data) => {
    return Object.keys(data).map((key) => {
      if (typeof data[key] === 'object') {
        return null; // Skip rendering nested objects
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
      children: (
        <>
          {client && renderClientDetails(client)}
          {client && client.location && (
            <div style={{ marginTop: '20px' }}>
              <strong>Location:</strong>
              <ViewMap location={client.location} /> {/* Pass location data to ViewMap */}
            </div>
          )}
        </>
      )
    },
    {
      key: '2',
      label: 'Members',
      children: "Member Page ready soon."
    },
  ];

  const collapseItems = [
    {
      key: '1',
      label: 'About',
      children: client && renderClientDetails(client)
    },
    {
      key: '2',
      label: 'Projects',
      children: <p>Projects content goes here.</p>
    },
    {
      key: '3',
      label: 'Members',
      children: <p>Members content goes here.</p>
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
      <h1>Client Details</h1>
      <Row gutter={[16, 16]}>
        <Col lg={12} md={24}>
          {client && (
            <div>
              <Collapse accordion>
                {collapseItems.map(item => (
                  <Panel key={item.key} header={item.label}>
                    {item.children}
                  </Panel>
                ))}
              </Collapse>
              {client.image && (
                <div style={{ marginTop: '20px' }}>
                  <strong>Image:</strong>
                  <Image
                    width={200}
                    src={`http://127.0.0.1:8000/storage/${client.image}`}
                    alt="Client"
                    style={{ borderRadius: '10px' }}
                  />
                </div>
              )}
              <div style={{ marginTop: '20px' }}>
                <Link to={`/client/edit/${id}`}>
                  <Button type="primary" style={{ marginRight: '10px' }}>Edit</Button>
                </Link>
                <Link to="/clients">
                  <Button>Back to Client</Button>
                </Link>
              </div>
            </div>
          )}
        </Col>
        <Col lg={12} md={24}>
          <Tabs defaultActiveKey="1">
            {tabItems.map(tab => (
              <TabPane tab={tab.label} key={tab.key}>
                {tab.children}
              </TabPane>
            ))}
          </Tabs>
        </Col>
      </Row>
    </div>
  );
}

export default ClientView;
