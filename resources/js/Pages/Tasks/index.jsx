import React, { useState, useEffect } from "react";
import { Space, Table, Button, Modal, Input, Select, Tag, DatePicker } from "antd";
import { EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getTasks } from "../../API";
import { Link } from "react-router-dom";
import TasksForm from "./form";

const { Option } = Select;

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
    options: ['none','inprogress', 'pending', 'completed']
  }
];

function Tasks() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [searchColumn, setSearchColumn] = useState("");

  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [createModalVisible, setCreateModalVisible] = useState(false);

  const [columns, setColumns] = useState([]);

  useEffect(() => {
    setLoading(true);
    getTasks().then((res) => {
      console.log("res", res);
      setDataSource(res.tasks);

      // Generate columns dynamically
      const firstRowKeys = Object.keys(res.tasks[0]);
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
            ) : key === 'createdDate' || key === 'dueDate' ? (
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
                  <Tag key={index} className={`badge-${tag}`}>{tag}</Tag>
                ))}
              </>
            );
          } else if (key === 'status' || key === 'paid') {
            return <Tag className={`badge-${text.toLowerCase()}`}>{text}</Tag>;
          } else {
            return text;
          }
        }
      }));

      generatedColumns.push({
        title: 'Action',
        dataIndex: 'action',
        fixed: 'right',
        render: (text, record) => (
          <Space size="middle">
            <Link to={`/tasks/view/${record.id}`}><EyeOutlined /></Link>
            <Link onClick={() => showDeleteModal(record)}><DeleteOutlined /></Link>
          </Space>
        ),
      });

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

    if (searchColumn === 'createdDate' || searchColumn === 'dueDate') {
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

  const showDeleteModal = (record) => {
    console.log('Delete:', record);
    setDeleteModalVisible(true);
  };

  const handleDelete = () => {
    console.log('Deleting:', selectedRows);
    setDeleteModalVisible(false);
  };

  const showCreateModal = () => {
    setCreateModalVisible(true);
  };

  const handleCreateCancel = () => {
    setCreateModalVisible(false);
  };

  return (
    <Space size={20} direction="vertical" style={{ width: '100%' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Tasks</b>
        </div>
        <Link to="/tasks/createform"> 
        <Button type="primary" htmlType="button" icon={<PlusOutlined />} >
          Create
        </Button>
        </Link>
      </div>

      <div style={{ overflowX: 'scroll' }}>
        <Table
          className="datatable tasks-table"
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

        <Modal
          title="Create Task"
          open={createModalVisible}
          onCancel={handleCreateCancel}
          footer={null}
        >
          <TasksForm />
        </Modal>
      </div>
    </Space>
  );
}

export default Tasks;
