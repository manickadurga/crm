
import React, { useState, useEffect, useCallback } from "react";
import { Space, Table, Input, Button, Modal, message, Card, Row, Col, Form } from "antd";
import { SearchOutlined, EyeOutlined, UnorderedListOutlined, WindowsOutlined, DeleteOutlined, EditOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { deleteItem, getDataFunction } from "../../../API";
import { Link, useNavigate } from "react-router-dom";
import Highlighter from "react-highlight-words";
import axios from "axios";

function Clients() {
  const [searchText, setSearchText] = useState('');
  const [dataSource, setDataSource] = useState([]);
  const [columns, setColumns] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [inviteModalVisible, setInviteModalVisible] = useState(false);
  const [selectedClient, setSelectedClient] = useState(null);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
  const [formValues, setFormValues] = useState({});
  const [clientIdToDelete, setClientIdToDelete] = useState(null);
  const navigate = useNavigate();

  const fields = [
    { name: 'name', label: 'Name', placeholder: 'Enter name', type: 'text' },
    { name: 'email', label: 'Email', placeholder: 'Enter email', type: 'email' },
    { name: 'phoneNumber', label: 'Phone Number', placeholder: 'Enter phone number', type: 'number' },
  ];

  useEffect(() => { 
    fetchClients(currentPage);
  }, [currentPage, searchText]);

  const fetchClients = (page) => {
    setLoading(true);
    getDataFunction('clients')
      .then((res) => {
        const sortedClients = res.clients.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        const filteredClients = sortedClients.filter((client) =>
          Object.keys(searchText).every((key) => {
            const clientValue = key.includes('.')
              ? key.split('.').reduce((obj, k) => (obj || {})[k], client)
              : client[key];

            return clientValue && clientValue.toString().toLowerCase().includes(searchText[key].toLowerCase());
          })
        );

        setDataSource(filteredClients);
        setTotalRecords(res.pagination.total);

        const generatedColumns = Object.keys(sortedClients[0] || {}).map((key) => ({
          title: (
            <div>
              {key}
              <Input
                placeholder={`Search ${key}`}
                value={searchText[key] || ''}
                onChange={(e) => handleSearch(e.target.value, key)}
                style={{ marginTop: 8, display: 'block' }}
              />
            </div>
          ),
          dataIndex: key,
          key: key,
          render: (text) => {
            return text;
          }
        }));

        setColumns(generatedColumns);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching Clients:', error);
        setLoading(false);
      });
  };

  // const deleteClient = async () => {
  //   try {
  //     if (!clientIdToDelete) {
  //       console.error('Client ID is undefined');
  //       return;
  //     }
  //     await axios.delete(`http://127.0.0.1:8000/jo-clients/${clientIdToDelete}`);
  //     console.log('Client deleted');
  //     setDataSource(dataSource.filter(client => client.id !== clientIdToDelete));

  //     message.success('Client Deleted Successfully');
      
  //     setDeleteModalVisible(false);
  //     fetchClients(currentPage);
  //     navigate('/clients');
  //   } catch (error) {
  //     console.error('There was an error deleting the client:', error);
  //     message.error('There was an error deleting the client');
  //   }
  // };
  
  const handleDelete = () => {
    deleteItem('jo-clients', selectedClient.id)
      .then(() => {
        message.success(`Client deleted successfully!`);
        fetchCustomers(currentPage); // Refresh the customers list
        setDeleteModalVisible(false);
        setSelectedClient(null);
      })
      .catch((error) => {
        console.error('Failed to delete customer:', error);
      });
  };


  const storeinviteClient = async (data) => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/joinvite-clients', data);
      return response.data;
    } catch (error) {
      console.error('Error storing client data:', error);
      throw error;
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    console.log(`Input Change - Name: ${name}, Value: ${value}`);
    setFormValues({
      ...formValues,
      [name]: value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    console.log('Form Values:', formValues);
    try {
      const response = await storeinviteClient(formValues);
      console.log('Client data stored successfully:', response);
    } catch (error) {
      console.error('Error submitting form:', error);
    }
  }
  // const handleInviteOk = async () => {
  //   try {
  //     await storeinviteClient();
  //     setInviteModalVisible(false);
  //     message.success('Invite sent successfully!');
  //   } catch (error) {
  //     message.error('Failed to send invite.');
  //   }
  // };

  const handleSearch = (value, key) => {
    const updatedSearchText = { ...searchText };
    if (value === '') {
      delete updatedSearchText[key];
    } else {
      updatedSearchText[key] = value;
    }
    setSearchText(updatedSearchText);
  };

  const showDeleteModal = (id) => {
    setClientIdToDelete(id);
    setDeleteModalVisible(true);
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  const showInviteModal = () => {
    setInviteModalVisible(true);
  };



  const onRowClick = (record) => {
    setSelectedClient(selectedClient && selectedClient.id === record.id ? null : record);
  };

  const renderColumns = () => (
    columns.map((column) => ({
      ...column,
      render: (text, record) => ({
        children: text,
        props: {
          style: {
            backgroundColor: selectedClient && selectedClient.id === record.id ? '#f0f0f0' : 'white',
            cursor: 'pointer',
          },
        },
      }),
    }))
  );

  const renderCards = () => (
    <Row gutter={16}>
      {dataSource.map((client) => (
        <Col key={client.id} span={6}>
          <Card
            title={client.name}
            onClick={() => onRowClick(client)}
            style={{ cursor: 'pointer', backgroundColor: selectedClient && selectedClient.id === client.id ? '#f0f0f0' : 'white' }}
          >
            {Object.keys(client).map((key) => (
              <p key={key}>
                <strong>{key}:</strong> {client[key]}
              </p>
            ))}
          </Card>
        </Col>
      ))}
    </Row>
  );

  return (
    <Space size={20} direction="vertical" style={{ width: '100%' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: '18px', marginLeft: '18px' }}> Clients </b>
        </div>

        <div style={{ display: 'flex', fontSize: '18px', marginLeft: '18px', gap: '5px' }}>
          {selectedClient && (
            <div style={{ gap: '2px' }}>
              <Link to={`/clients/view/${selectedClient.id}`}>
                <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                  <EyeOutlined />
                </Button>
              </Link>
              <Link to={`/clients/edit/${selectedClient.id}`}>
                <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                  <EditOutlined />
                </Button>
              </Link>
              <Button 
                type="link" 
                onClick={() => showDeleteModal(selectedClient.id)} 
                style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}
              >
                <DeleteOutlined />
              </Button>
            </div>
          )}

          <Button type="primary" htmlType="button" onClick={showInviteModal}>
            Invite
          </Button>
          <Link to="/clients/ClientsForm">
            <Button type="primary" htmlType="button" icon={<PlusOutlined />} style={{ marginLeft: '10px', marginRight: '10px' }}>
              Add
            </Button>
          </Link>
          <Button
            style={{ marginRight: '10px' }}
            type={viewMode === 'table' ? 'primary' : 'default'}
            onClick={() => setViewMode('table')}
          >
            <WindowsOutlined />
          </Button>
          <Button
            type={viewMode === 'card' ? 'primary' : 'default'}
            onClick={() => setViewMode('card')}
          >
            <UnorderedListOutlined />
          </Button>
        </div>
      </div>
      {viewMode === 'table' ? (
        <Table
          dataSource={dataSource}
          columns={renderColumns()}
          rowKey="id"
          pagination={{
            current: currentPage,
            pageSize: 10,
            total: totalRecords,
            onChange: handlePageChange,
          }}
          loading={loading}
          onRow={(record) => ({
            onClick: () => onRowClick(record),
          })}
        />
      ) : (
        renderCards()
      )}

      <Modal
        title="Delete Client"
        visible={deleteModalVisible}
        onOk={handleDelete}
        onCancel={() => setDeleteModalVisible(false)}
      >
        <p>Are you sure you want to delete this client?</p>
      </Modal>

      <Modal
        title="Invite Client"
        visible={inviteModalVisible}
        onOk={handleSubmit}
        onCancel={() => setInviteModalVisible(false)}
      >
        <Form layout="vertical">
          {fields.map(field => (
            <Form.Item key={field.name} label={field.label}>
              <Input
                type={field.type}
                name={field.name}
                placeholder={field.placeholder}
                value={formValues[field.name] || ''}
                onChange={handleInputChange}
              />
            </Form.Item>
          ))}
        </Form>
      </Modal>
    </Space>
  );
}

export default Clients;
