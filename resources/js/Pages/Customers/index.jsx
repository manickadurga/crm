import React, { useState, useEffect } from "react";
<<<<<<< HEAD
import { Space, Table, Input, Button, Modal, message } from "antd";
import { SearchOutlined, EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getCustomers, deleteCustomer } from "../../API";
import { Link,useNavigate } from "react-router-dom";
=======
import { Space, Table, Input, Button, Modal, message, Card, Row, Col, Form } from "antd";
import { SearchOutlined, EyeOutlined, UnorderedListOutlined, WindowsOutlined, DeleteOutlined, EditOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getCustomers, deleteCustomer } from "../../API";
import { Link, useNavigate } from "react-router-dom";
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
import Highlighter from "react-highlight-words";

function Customers() {
  const [searchText, setSearchText] = useState({});
  const [dataSource, setDataSource] = useState([]);
<<<<<<< HEAD
  const [searchText, setSearchText] = useState("");
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [columns, setColumns] = useState([]);
 
=======
  const [columns, setColumns] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [inviteModalVisible, setInviteModalVisible] = useState(false);
  const [selectedCustomer, setSelectedCustomer] = useState(null);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
  const [formValues, setFormValues] = useState({});
  const [fields, setFields] = useState([
    { name: 'name', label: 'Name', placeholder: 'Enter name', type: 'text' },
    { name: 'email', label: 'Email', placeholder: 'Enter email', type: 'email' },
    { name: 'phoneNumber', label: 'Phone Number', placeholder: 'Enter phone number', type: 'tel' },
  ]);
  //  const [data,setData]=useState()
  const navigate = useNavigate();

  useEffect(() => {
    fetchCustomers(currentPage);
  }, [currentPage]);
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98

  // Reset to page 1 when searchText changes
  useEffect(() => {
    fetchCustomers(1);
  }, [searchText]);

  const fetchCustomers = (page) => {
    setLoading(true);
<<<<<<< HEAD
    getCustomers()
      .then((res) => {
        // Sort customers by created_at or id in descending order
        const sortedCustomers = res.customers.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        setDataSource(sortedCustomers);

        // Generate columns dynamically
        const columnHeadings = {
          image: 'Image',
          name: 'Name',
          primary_email: 'Primary Email',
          primary_phone: 'Primary Phone',
          website: 'Website',
          fax: 'Fax',
          fiscal_information: 'Fiscal Information',
          projects: 'Projects',
          contact_type: 'Contact Type',
          tags: 'Tags',
          location: 'Location',
          type: 'Type',
          type_suffix: 'Type Suffix'
        };

        const generatedColumns = Object.keys(columnHeadings).map((key) => ({
          title: columnHeadings[key],
          dataIndex: key,
          render: (text) => (
            <Highlighter
              highlightStyle={{ backgroundColor: "#ffc069", padding: 0 }}
              searchWords={[searchText]}
              autoEscape
              textToHighlight={text ? text.toString() : ""}
            />
          ),
        }));
        generatedColumns.push({
          title: 'Action',
          dataIndex: 'action',
          fixed: 'right',
          render: (text, record) => (
            <Space size="middle">
              <Link type="primary" to="/customers/view" onClick={() => handleView(record)}><EyeOutlined /></Link>
              <Link type="danger" onClick={() => showDeleteModal(record)}><DeleteOutlined /></Link>
            </Space>
          ),
        });

        setColumns(generatedColumns);

=======
    getCustomers(page)
      .then((res) => {
        const sortedCustomers = res.customers.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        // Filter dataSource based on searchText
        const filteredCustomers = sortedCustomers.filter((customer) =>
          Object.keys(searchText).every((key) => {
            const customerValue = key.includes('.')
              ? key.split('.').reduce((obj, k) => (obj || {})[k], customer)
              : customer[key];

            return customerValue && customerValue.toString().toLowerCase().includes(searchText[key].toLowerCase());
          })
        );

        setDataSource(filteredCustomers);
        setTotalRecords(res.pagination.total);

        // Generate columns dynamically based on customer object keys
        const generatedColumns = Object.keys(sortedCustomers[0] || {}).map((key) => ({
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
            return text; // Default rendering
          }
        }));

        setColumns(generatedColumns);
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching customers:', error);
        setLoading(false);
      });
<<<<<<< HEAD
  }, [searchText]);

  const handleSearch = (value) => {
    setSearchText(value);
  };

  const filteredData = dataSource.filter((record) => {
    return Object.values(record).some((value) =>
      value.toString().toLowerCase().includes(searchText.toLowerCase())
    );
  });
  const navigate = useNavigate();

  const handleView = (record) => {
    navigate("/customers/view", { state: { customer: record } });
  };
  
  
  const showDeleteModal = (record) => {
    setSelectedRows([record]);
    setDeleteModalVisible(true);
  };
  
  const handleDelete = () => {
    const customerToDelete = selectedRows[0];
    deleteCustomer(customerToDelete.id)
      .then(() => {
        const updatedDataSource = dataSource.filter((record) => record.id !== customerToDelete.id);
        setDataSource(updatedDataSource);
        setSelectedRowKeys([]);
        setSelectedRows([]);
        setDeleteModalVisible(false);
        message.success(`Customer Details deleted successfully!`);
=======
  };

  const handleSearch = (value, key) => {
    const updatedSearchText = { ...searchText };
    if (value === '') {
      delete updatedSearchText[key];
    } else {
      updatedSearchText[key] = value;
    }
    setSearchText(updatedSearchText);
  };



  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormValues({
      ...formValues,
      [name]: value
    });
  };


  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  const showDeleteModal = () => {
    setDeleteModalVisible(true);
  };

  const showInviteModal = () => {
    setInviteModalVisible(true);
  };


  const handleOk = () => {
    console.log(formValues);
    setInviteModalVisible(false);
  };

  const handleDelete = () => {
    deleteCustomer(selectedCustomer.id)
      .then(() => {
        message.success(`Customer deleted successfully!`);
        fetchCustomers(currentPage); // Refresh the customers list
        setDeleteModalVisible(false);
        setSelectedCustomer(null);
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
      })
      .catch((error) => {
        console.error('Failed to delete customer:', error);
      });
  };

  const onRowClick = (record) => {
    setSelectedCustomer(selectedCustomer && selectedCustomer.id === record.id ? null : record);
  };
  const renderCards = () => {
    if (!dataSource || dataSource.length === 0) {
      return <p>No customers available.</p>;
    }

    return (
      <Row gutter={16}>
        {dataSource.map((customer) => (
          <Col key={customer.id} span={6}>
            <Card
              title={customer.name}
              onClick={() => onRowClick(customer)}
              style={{ cursor: 'pointer', backgroundColor: selectedCustomer && selectedCustomer.id === customer.id ? '#f0f0f0' : 'white' }}
            >
              {Object.keys(customer).map((key) => (
                <p key={key}>
                  <strong>{key}:</strong> {customer[key]}
                </p>
              ))}
            </Card>
          </Col>
        ))}
      </Row>
    );
  };
  const renderColumns = () => {
    if (!columns || columns.length === 0) {
      return null;
    }

    return columns.map((column) => ({
      ...column,
      render: (text, record) => ({
        children: text,
        props: {
          style: {
            backgroundColor: selectedCustomer && selectedCustomer.id === record.id ? '#f0f0f0' : 'white',
            cursor: 'pointer',
          },
        },
      }),
    }));
  };


  return (
<<<<<<< HEAD
    <Space size={20} direction="vertical" style={{ width: '-webkit-fill-available' }}>
=======
    <Space size={20} direction="vertical" style={{ width: '100%' }}>
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
<<<<<<< HEAD
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Customers</b>
=======
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Customers {totalRecords}</b>
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
        </div>

        <div style={{ display: 'flex', fontSize: '18px', marginLeft: '18px', gap: '5px' }}>
          {selectedCustomer && (
            <div style={{ gap: '2px' }}>
              <Link to={`/customers/view/${selectedCustomer.id}`}>
                <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                  <EyeOutlined />
                </Button>
              </Link>
              <Link to={`/customers/edit/${selectedCustomer.id}`}>
                <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                  <EditOutlined />
                </Button>
              </Link>
              <Button type="link" onClick={showDeleteModal} style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                <DeleteOutlined />
              </Button>
            </div>
          )}

          <Button type="primary" htmlType="button" onClick={showInviteModal}>
            Invite
          </Button>
          <Link to="/customers/createform">
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
      <div style={{ overflowX: 'scroll' }}>
<<<<<<< HEAD
        <Table
          className="datatable customers-table"
          loading={loading}
          columns={columns}
          dataSource={filteredData}
          scroll={{ x: true, y: 340 }}
          pagination={{
            pageSize: 8,
          }}
        />
=======
        {viewMode === 'table' ? (

          <Table
            className="datatable customers-table"
            loading={loading}
            columns={renderColumns()}
            dataSource={dataSource}
            pagination={{
              current: currentPage,
              pageSize: 10,
              total: totalRecords,
              onChange: handlePageChange,
            }}
            onRow={(record) => ({
              onClick: () => onRowClick(record),
              style: { cursor: 'pointer' },
            })}
          />
        ) : (
          renderCards()
        )}
        <h4>Total Customers: {totalRecords}</h4>
        <Modal
          title="Invite Contact"
          visible={inviteModalVisible}
          onCancel={() => setInviteModalVisible(false)}
          onOk={handleOk}
          okText="Email invite"
          cancelText="Cancel"
        >
          <Form layout="vertical">
            {fields.map(field => (
              <Form.Item key={field.name} label={field.label}>
                <Input
                  name={field.name}
                  type={field.type}
                  value={formValues[field.name] || ''}
                  onChange={handleInputChange}
                  placeholder={field.placeholder}
                />
              </Form.Item>
            ))}
          </Form>
        </Modal>
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
        <Modal
          title="Delete Confirmation"
          open={deleteModalVisible}
          onCancel={() => setDeleteModalVisible(false)}
          onOk={handleDelete}
          okText="Delete"
          cancelText="Cancel"
        >
<<<<<<< HEAD
          Are you sure you want to delete the selected item(s)?
=======
          Are you sure you want to delete the selected item?
>>>>>>> 99fb547e93f886b0121bb68fcec4abab03d18b98
        </Modal>
      </div>
    </Space>
  );
}

export default Customers;
