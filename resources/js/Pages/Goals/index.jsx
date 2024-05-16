import React, { useState, useEffect } from "react";
import { Space, Typography, Tag, Table, Avatar, Input, Button, Checkbox, Modal, DatePicker, Collapse, Select } from "antd";
import { SearchOutlined, EyeOutlined, DeleteOutlined } from "@ant-design/icons";
import { getGoals, getInventory } from "../../API";
import { Link } from "react-router-dom";
import Highlighter from "react-highlight-words";

const { Panel } = Collapse;
const { RangePicker } = DatePicker;
const { Option } = Select;

function Goals() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");

  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);

  const [columns, setColumns] = useState([]);

  useEffect(() => {
    setLoading(true);
    getGoals().then((res) => {
      console.log("res", res);
      setDataSource(res.invoices);

      // Generate columns dynamically
      const firstRowKeys = Object.keys(res.invoices[0]);
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

      const tagsColumnIndex = generatedColumns.findIndex((column) => column.dataIndex === 'tags');

      // Modify the 'Tags' column to render each tag as a badge tag
      if (tagsColumnIndex !== -1) {
        generatedColumns[tagsColumnIndex].render = (tags) => (
          <>
            {tags.map((tag, index) => (
              <Tag key={index} className={`badge-${tag}`}>{tag}</Tag>
            ))}
          </>
        );
      }
      const statusColumnIndex = generatedColumns.findIndex((column) => column.dataIndex === 'status');
      if (statusColumnIndex !== -1) {
        generatedColumns[statusColumnIndex].render = (status) => (
          <Tag className={`badge-${status.toLowerCase()}`}>{status}</Tag>
        );
      }

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
        width: '100px'
      });

      setColumns(generatedColumns);

      setLoading(false);
    });
  }, [searchText]);

  const handleSearch = (value) => {
    setSearchText(value);
  };

  const handleDateSearch = (dates, dateStrings, dataIndex, confirm) => {
    setSearchText(dateStrings[0] + ' - ' + dateStrings[1]);
    confirm();
  };

  const filteredData = dataSource.filter((record) => {
    return Object.values(record).some((value) =>
      value.toString().toLowerCase().includes(searchText.toLowerCase())
    );
  });
  
  const handleView = (record) => {
    console.log('View:', record);
  };

  const showDeleteModal = (record) => {
    console.log('Delete:', record);
    setDeleteModalVisible(true);
  };

  const handleDelete = () => {
    console.log('Deleting:', selectedRows);
    setDeleteModalVisible(false);
  };

  return (
    <Space size={20} direction="vertical" style={{ width: '-webkit-fill-available' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'right', gap: '1rem' }}>
        <Input
          placeholder="Search all columns"
          prefix={<SearchOutlined />}
          value={searchText}
          onChange={(e) => handleSearch(e.target.value)}
          style={{ width: 300 }}
        />
        <Link to="/goals/createform">
          <Button type="primary" htmlType="button">
            Add
          </Button>
        </Link>
      </div>
      <Collapse bordered={false}>
        <Panel header="Advanced Search" key="1">
          <Input.Search
            placeholder="Search Invoice Number"
            onSearch={value => handleSearch(value)}
            style={{ marginBottom: 8 }}
          />
          <RangePicker
            placeholder={['Start Date', 'End Date']}
            onChange={handleDateSearch}
            style={{ marginBottom: 8 }}
          />
          <Input.Search
            placeholder="Search Total Value"
            onSearch={value => handleSearch(value)}
            style={{ marginBottom: 8 }}
          />
          <Input.Search
            placeholder="Search Tax"
            onSearch={value => handleSearch(value)}
            style={{ marginBottom: 8 }}
          />
          <Input.Search
            placeholder="Search Tax 2"
            onSearch={value => handleSearch(value)}
            style={{ marginBottom: 8 }}
          />
          <Input.Search
            placeholder="Search Discount"
            onSearch={value => handleSearch(value)}
            style={{ marginBottom: 8 }}
          />
          <Select
            placeholder="Filter by Contact"
            style={{ width: '100%', marginBottom: 8 }}
            onChange={value => handleSearch(value)}
          >
            {/* Options for contacts */}
          </Select>
          <Select
            placeholder="Filter by Tags"
            style={{ width: '100%', marginBottom: 8 }}
            onChange={value => handleSearch(value)}
          >
            {/* Options for tags */}
          </Select>
          <Select
            placeholder="Sort by Paid Status"
            style={{ width: '100%', marginBottom: 8 }}
            onChange={value => handleSearch(value)}
          >
            {/* Options for paid status */}
          </Select>
          <Select
            placeholder="Sort by Status"
            style={{ width: '100%', marginBottom: 8 }}
            onChange={value => handleSearch(value)}
          >
            {/* Options for status */}
          </Select>
        </Panel>
      </Collapse>
      <div style={{ overflowX: 'scroll' }}>
        <Table
          className="datatable goals-table"
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
          visible={deleteModalVisible}
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

export default Goals;
