
import React, { useState, useEffect } from "react";
import { Space, Table, Button, Modal, Dropdown,Input, Form,Select, Menu,  Tag, DatePicker, Tooltip, Typography, Avatar, Row, Col, Card } from "antd";
import { EyeOutlined, DeleteOutlined, DownOutlined, FolderOutlined, ArrowLeftOutlined, EditOutlined, PlusOutlined, UnorderedListOutlined, WindowsOutlined, UserOutlined } from "@ant-design/icons";
import { getProposals } from "../../API";
import { Link } from "react-router-dom";
// import TasksForm from "./form";

const { Option } = Select;
const { TextArea } = Input;
const selectOptions = [
  {
    title: "Tags",
    dataIndex: "tags",
    options: ['none','urgent', 'important', 'pending', 'completed']
  },
  {
    title: "Paid Status",
    dataIndex: "paid",
    options: ['none','paid', 'unpaid']
  },
  {
    title: "Status",
    dataIndex: "status",
    options: ['none','sent', 'accepted']
  }
];

const menu = (
    <Menu>
      <Menu.Item key="1">
        <Link to="/report">
          Report
        </Link>
      </Menu.Item>
      <Menu.Item key="2">
        <Link to="/chat">
          Chat
        </Link>
      </Menu.Item>
    </Menu>
  );


function Reports() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [searchColumn, setSearchColumn] = useState("");
  const [selectedProposals, setSelectedProposals] = useState(null);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [createModalVisible, setCreateModalVisible] = useState(false);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
  const [columns, setColumns] = useState([]);
  const [formValues, setFormValues] = useState({});
  const [searchQuery, setSearchQuery] = useState('');
  const [submittedNames, setSubmittedNames] = useState([]);
//   const [createModalVisible, setCreateModalVisible] = useState(false);
  const [fields] = useState([
    { name: 'name', label: 'Name', placeholder: 'Enter name', type: 'text' },
    { name: 'details', label: 'Details', placeholder: 'Enter details', type: 'textarea' },
  ]);

  useEffect(() => {
    setLoading(true);
    getProposals().then((res) => {
      console.log("res", res);
      setDataSource(res.proposals);

      // Generate columns dynamically
      const firstRowKeys = Object.keys(res.proposals[0]);
      const generatedColumns = firstRowKeys.map((key, index) => ({
        title: (
          <div>
            <div style={{borderBottom:'1px solid #eee',marginBottom:8,paddingBottom:4, textAlign:'center'}}>{key.charAt(0).toUpperCase() + key.slice(1)}</div>
            {key === 'tags' ? (
              <Select
                placeholder={`Filter by ${key}`}
                style={{ width: '100%' }}
                onChange={value => handleColumnSearch(value, key)}
              >
                {selectOptions.find(option => option.dataIndex === key).options.map((option, optionIndex) => (
                  <Option key={optionIndex} value={option}>{option}</Option>
                ))}
              </Select>
            ) : key === 'paid' || key === 'status' ? (
              <Select
                placeholder={`Filter by ${key}`}
                style={{ width: '100%' }}
                onChange={value => handleColumnSearch(value, key)}
              >
                {selectOptions.find(option => option.dataIndex === key).options.map((option, optionIndex) => (
                  <Option key={optionIndex} value={option}>{option}</Option>
                ))}
              </Select>
            ) : key === 'date' || key === 'dueDate' ? (
              <DatePicker
                style={{ width: '100%' }}
                onChange={(date, dateString) => handleDateSearch(date, dateString, key)}
              />
            ) : (
              <Input
                placeholder={`Search ${key}`}
                onChange={e => handleColumnSearch(e.target.value, key)}
                style={{ width: '100%' }}
              />
            )}
          </div>
        ),
        dataIndex: key,
        render: (text) => {
          if (key === 'tags') {
            return (
              <>
                {text.map((tag, index) => (
                  <Tag key={index} className={`badge-${tag}`} style={{minWidth:100}} >{tag}</Tag>
                ))}
              </>
            );
          } else if (key === 'contactName') {
            return <Space><Avatar size='small' icon={<UserOutlined />} />{text}</Space>
          } else if (key === 'jobUrl') {
            return <Link to={text}>{text}</Link>
          } else if (key === 'status' || key === 'paid') {
            return <Tag className={`badge-${text.toLowerCase()}`}>{text}</Tag>;
          } else {
            return text;
          }
        }
      }));

      setColumns(generatedColumns);
      setLoading(false);
    });

  }, []);

  const handleDateSearch = (date, dateString, dataIndex) => {
    setSearchText(dateString);
    setSearchColumn(dataIndex);
  };

  const handleColumnSearch = (value, dataIndex) => {
    setSearchText(value);
    setSearchColumn(dataIndex);
    if (value === 'none') {
      setSearchText("");
      setSearchColumn("");
    }
  };


  const filteredData = dataSource.filter((record) => {
    if (!searchText || !searchColumn) return true;
    const value = record[searchColumn];
    if (!value) return false;

    if (searchColumn === 'date' || searchColumn === 'dueDate') {
      const dateValue = new Date(value).getTime();
      const searchTextValue = new Date(searchText).getTime();
      return dateValue === searchTextValue;
    } else if (searchColumn === 'tags') {
      return value.some(tag => tag.toLowerCase().includes(searchText.toLowerCase()));
    } else if (searchColumn === 'status' || searchColumn === 'paid') {
      return value.toLowerCase().includes(searchText.toLowerCase());
    } else {
      return value.toString().toLowerCase().includes(searchText.toLowerCase());
    }
  });

  const showDeleteModal = () => {
    setDeleteModalVisible(true);
  };

  const handleDelete = () => {
    const newDataSource = dataSource.filter((item) => !selectedRowKeys.includes(item.id));
    setDataSource(newDataSource);
    setSelectedRowKeys([]);
    setDeleteModalVisible(false);
  };


  const showCreateModal = () => {
    setCreateModalVisible(true);
  };

  const handleCreateCancel = () => {
    setCreateModalVisible(false);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormValues({ ...formValues, [name]: value });
  };

  const handleSearchChange = (e) => {
    setSearchQuery(e.target.value);
  };

  const handleOk = () => {
    if (formValues.name) {
      setSubmittedNames([...submittedNames, formValues.name]);
    }
    setFormValues({});
    setCreateModalVisible(false);
  };

  const filteredNames = submittedNames.filter(name =>
    name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleRowClick = (record) => {
    setSelectedProposals(prevInvoice => {
      // Toggle selection if clicking on the same invoice
      if (prevInvoice && prevInvoice.id === record.id) {
        return null; // Deselect
      } else {
        return record; // Select the new invoice
      }
    });
  };

  const onSelectChange = (newSelectedRowKeys) => {
    setSelectedRowKeys(newSelectedRowKeys);
  };

  const rowSelection = {
    selectedRowKeys,
    onChange: onSelectChange,
  };

  const hasSelected = selectedRowKeys.length > 0;

  const renderCardsComponent = (record) => (
    <Row gutter={16}>
      {dataSource.map((invoice) => (
        <Col key={invoice.id} span={8}>
          <Card
            title={`Invoice ${invoice.id}`}
            onClick={() => handleRowClick(invoice)}
            style={{
              cursor: 'pointer',
              backgroundColor: selectedProposals && selectedProposals.id === invoice.id ? '#f0f0f0' : 'white'
            }}
          >
            {Object.keys(invoice).map((key) => (
              <p key={key}>
                <strong>{key}:</strong> {invoice[key]}
              </p>
            ))}
          </Card>
        </Col>
      ))}
    </Row>
  );

  return (
    <>
    <div style={{ display: 'flex', alignItems: 'center',justifyContent:'space-between' }}>
    <div>
    <Link to="/">
      <Button shape="circle" htmlType="button" size="small">
        <ArrowLeftOutlined />
      </Button>
    </Link>
    <b style={{ fontSize: '18px', marginLeft: '18px' }}>Reports</b>

    </div>
     <div>
     <Dropdown overlay={menu}>
            <Button type="primary">
              <PlusOutlined /> Add Report <DownOutlined />
            </Button>
          </Dropdown>
     </div>
  </div>
    <Row>
       
      <Col lg={6} >
      <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <p>Folders</p>
        <Button onClick={showCreateModal} type="primary">+</Button>
      </div>
      <Input
            placeholder="Search names"
            value={searchQuery}
            onChange={handleSearchChange}
            style={{ marginBottom: '10px' }}
          />
      {submittedNames.length > 0 && (
        <>
        
          <div style={{  }}>
            {filteredNames.map((name, index) => (
              <div key={index} style={{ display: 'flex', alignItems: 'center', gap: '3px' }}>
                <FolderOutlined />
                <p>{name}</p>
              </div>
            ))}
          </div>
        </>
      )}
       </div>
      
      </Col>
    <Col lg={18} size={20} direction="vertical" style={{ width: '100%' }}>

      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        
        <div>
          {selectedProposals && (
            <>
              <Link to={`/invoices/view/${selectedProposals.id}`}>
                <Button type="link" style={{ marginRight: '8px', border: '1px solid #ccc', background: 'white' }}>
                  <EyeOutlined /> View
                </Button>
              </Link>
              <Link to={`/invoices/edit/${selectedProposals.id}`}>
                <Button type="link" style={{ marginRight: '8px', border: '1px solid #ccc', background: 'white' }}>
                  <EditOutlined /> Edit
                </Button>
              </Link>
              <Button type="link" danger onClick={showDeleteModal} style={{ marginRight: '8px', border: '1px solid #ccc', background: 'white' }}>
                <DeleteOutlined /> Delete
              </Button>
            </>
          )}
          {/* <Button onClick={showCreateModal} type="primary">
            <PlusOutlined /> New Proposal
          </Button> */}
           
        </div>
      </div>

      <div direction="vertical" size="middle" style={{right:0}}>
        <Button onClick={() => setViewMode('table')} style={{ marginRight: '8px' }}>
          <UnorderedListOutlined /> 
        </Button>
        <Button onClick={() => setViewMode('card')}>
          <WindowsOutlined /> 
        </Button>
      </div>
      
      {viewMode === 'table' ? (
        <Table
          rowSelection={rowSelection}
          columns={columns}
          dataSource={filteredData}
          loading={loading}
          rowKey={(record) => record.id}
          onRow={(record) => ({
            onClick: () => handleRowClick(record),
          })}
        />
      ) : (
        renderCardsComponent()
      )}
      <Modal
        title="Delete Proposals"
        visible={deleteModalVisible}
        onOk={handleDelete}
        onCancel={() => setDeleteModalVisible(false)}
      >
        <p>Are you sure you want to delete the selected proposals?</p>
      </Modal>
      {/* <Modal
        title="Create Proposal"
        visible={createModalVisible}
        onCancel={handleCreateCancel}
        footer={null}
      >
      </Modal> */}
      <Modal
        title="Invite Contact"
        visible={createModalVisible}
        onCancel={handleCreateCancel}
        onOk={handleOk}
        okText="Save"
        cancelText="Cancel"
      >
        <Form layout="vertical">
          {fields.map(field => (
            <Form.Item key={field.name} label={field.label}>
              {field.type === 'textarea' ? (
                <Input.TextArea
                  name={field.name}
                  value={formValues[field.name] || ''}
                  onChange={handleInputChange}
                  placeholder={field.placeholder}
                />
              ) : (
                <Input
                  name={field.name}
                  type={field.type}
                  value={formValues[field.name] || ''}
                  onChange={handleInputChange}
                  placeholder={field.placeholder}
                />
              )}
              </Form.Item>
          ))}
        </Form>
      </Modal>
    </Col>  
    </Row>

    </>

  );
}

export default Reports;
