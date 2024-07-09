import React, { useState, useEffect } from "react";
import { Space, Table, Input, Button, Modal, message } from "antd";
import { SearchOutlined, EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getCustomers, deleteCustomer } from "../../API";
import { Link,useNavigate } from "react-router-dom";
import Highlighter from "react-highlight-words";

function Customers() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [columns, setColumns] = useState([]);
 

  useEffect(() => {
    setLoading(true);
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

        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching customers:', error);
        setLoading(false);
      });
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
      })
      .catch((error) => {
        console.error('Failed to delete customer:', error);
      });
  };

  return (
    <Space size={20} direction="vertical" style={{ width: '-webkit-fill-available' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Customers</b>
        </div>
        <div>
          <Input
            placeholder="Search all columns"
            prefix={<SearchOutlined />}
            value={searchText}
            onChange={(e) => handleSearch(e.target.value)}
            style={{ width: 300 }}
          />
          &nbsp;
          <Link to="/customers/createform">
            <Button type="primary" htmlType="button" icon={<PlusOutlined />}>
              Add
            </Button>
          </Link>
        </div>
      </div>
      <div style={{ overflowX: 'scroll' }}>
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
        <Modal
          title="Delete Confirmation"
          open={deleteModalVisible}
          onCancel={() => setDeleteModalVisible(false)}
          onOk={handleDelete}
          okText="Delete"
          cancelText="Cancel"
        >
          Are you sure you want to delete the selected item(s)?
        </Modal>
      </div>
    </Space>
  );
}

export default Customers;
