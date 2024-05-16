import React, { useState, useEffect } from "react";
import { Space, Typography, Table, Avatar, Input, Button, Checkbox, Modal } from "antd";
import { SearchOutlined, EyeOutlined, DeleteOutlined } from "@ant-design/icons";
import { getCustomers } from "../../API";
import { Link } from "react-router-dom";
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
    getCustomers().then((res) => {
      setDataSource(res.users);

      // Generate columns dynamically
      const firstRowKeys = Object.keys(res.users[0]);
      const generatedColumns = firstRowKeys.map((key, index) => ({
        title: key.charAt(0).toUpperCase() + key.slice(1),
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
            <Link type="primary" onClick={() => handleView(record)}><EyeOutlined /></Link>
            <Link type="danger" onClick={() => showDeleteModal(record)}><DeleteOutlined /></Link>
          </Space>
        ),
      });

      setColumns(generatedColumns);

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
  
   // Function to handle view action
   const handleView = (record) => {
    // Implement logic to handle view action
    console.log('View:', record);
  };

  // Function to show delete confirmation modal
  const showDeleteModal = (record) => {
    // Implement logic to show delete confirmation modal
    console.log('Delete:', record);
    setDeleteModalVisible(true);
  };

  // Function to handle delete action
  const handleDelete = () => {
    console.log('Deleting:', selectedRows);
    setDeleteModalVisible(false);
  };

  return (
    <Space size={20} direction="vertical" style={{width: '-webkit-fill-available'}}>
      <div style={{position:'sticky', display:'flex',justifyContent:'right',gap:'1rem'}}>
      <Input
        placeholder="Search all columns"
        prefix={<SearchOutlined />}
        value={searchText}
        onChange={(e) => handleSearch(e.target.value)}
        style={{ width: 300 }}
      />
      <Link to="/customers/createform">
        <Button type="primary" htmlType="button">
          {" "}Add{" "}
        </Button>
      </Link>
      </div>
      <div style={{overflowX:'scroll'}}>
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
