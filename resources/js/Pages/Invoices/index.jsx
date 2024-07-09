import React, { useState, useEffect } from "react";
import { Space, Table, Button, Modal, Collapse, Input, Select, Tag, DatePicker, List, Avatar, Tooltip, Typography } from "antd";
import { EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getGoals } from "../../API";
import { Link } from "react-router-dom";

const { Panel } = Collapse;
const { Option } = Select;
const {RangePicker} = DatePicker;
const selectOptions = [
  {
    title: "Tags",
    dataIndex: "tags",
    options: ['none', 'urgent', 'important', 'pending', 'completed']
  },
  {
    title: "Paid Status",
    dataIndex: "paid",
    options: ['none', 'paid', 'unpaid']
  },
  {
    title: "Status",
    dataIndex: "status",
    options: ['none', 'inprogress', 'pending', 'completed']
  }
];

function Invoices() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [searchColumn, setSearchColumn] = useState("");

  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);

  const [columns, setColumns] = useState([]);

  const [comments, setComments] = useState([]);

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
            <Link to={`/invoices/view/${record.id}`}><EyeOutlined /></Link>
            <Link onClick={() => showDeleteModal(record)}><DeleteOutlined /></Link>
          </Space>
        ),
      });

      setColumns(generatedColumns);

      setLoading(false);
    });
    const dummyData = [
      {
        id: 1,
        avatar: 'https://example.com/avatar1.png',
        name: 'John Doe',
        dateTime: '2024-05-15 10:30 AM',
        commentTitle: 'Great work!',
        commentDescription: 'This is awesome!',
      },
      {
        id: 2,
        avatar: 'https://example.com/avatar2.png',
        name: 'Jane Smith',
        dateTime: '2024-05-14 02:45 PM',
        commentTitle: 'Impressive',
        commentDescription: 'Keep it up!',
      },
      {
        id: 3,
        avatar: 'https://example.com/avatar1.png',
        name: 'Tommy',
        dateTime: '2024-05-12 10:30 AM',
        commentTitle: 'Great ba!',
        commentDescription: 'This is awesome!',
      },
      {
        id: 4,
        avatar: 'https://example.com/avatar2.png',
        name: 'marc',
        dateTime: '2024-05-12 02:45 PM',
        commentTitle: 'Impressive lyy',
        commentDescription: 'Keep it up!',
      },
    ];

    setComments(dummyData);
  }, []);

  const handleDateRangeSearch = (dates, dateStrings, dataIndex) => {
    setSearchText(dateStrings[0] + ' - ' + dateStrings[1]);
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
  
    if (searchColumn === 'invoiceDate' || searchColumn === 'dueDate') {
      const [startDate, endDate] = searchText.split(' - ');
      const dateValue = new Date(value).getTime();
      const startDateValue = startDate ? new Date(startDate).getTime() : 0;
      const endDateValue = endDate ? new Date(endDate).getTime() : Number.MAX_VALUE;
      return dateValue >= startDateValue && dateValue <= endDateValue;
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
  

  return (
    <Space size={20} direction="vertical" style={{ width: '-webkit-fill-available' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{display:'flex', alignItems:'center'}}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{fontSize:'18px', marginLeft:'18px'}}>Invoices</b>
        </div>
        <Link to="/invoices/createform">
          <Button type="primary" htmlType="button" icon={<PlusOutlined />}>
            Create
          </Button>
        </Link>
      </div>
      <Collapse bordered={false} style={{backgroundColor:'#fff'}}>
        <Panel header="Advanced Search" key="1">
          <div style={{display:'flex',flexWrap:'wrap',gap:'8px',rowGap:'8px',}}>
            {columns.slice(0, -6).map((column, index) => ( //last 4 column name cut panrthuku
              <Input
                key={index}
                placeholder={`Search ${column.title}`}
                // onSearch={value => handleColumnSearch(value, column.dataIndex)}
                onChange={e => handleColumnSearch(e.target.value, column.dataIndex)}
                style={{maxWidth:'240px'}}
              />
            ))}
            <RangePicker
              style={{ maxWidth:'240px'}}
              onChange={(dates, dateStrings) => handleDateRangeSearch(dates, dateStrings, 'invoiceDate')}
            />
            <RangePicker
              style={{ maxWidth:'240px'}}
              onChange={(dates, dateStrings) => handleDateRangeSearch(dates, dateStrings, 'dueDate')}
            />
            {selectOptions.map((select, index) => (
              <Select
                key={index}
                placeholder={`Filter by ${select.title}`}
                style={{ maxWidth:'240px'}}
                onChange={value => handleColumnSearch(value, select.dataIndex)}
              >
                {select.options.map((option, optionIndex) => (
                  <Option key={optionIndex} value={option}>{option}</Option>
                ))}
              </Select>
            ))}
          </div>
        </Panel>
        <Panel header="History" key="2">
          <div style={{ display: 'flex',gap:'16px' }}>
            <div style={{ width: '40%' }}>
              {/* Left side: Title and Description Input */}
              <Input type='text' placeholder="Title" style={{ marginBottom: '10px' }} />
              <Input.TextArea placeholder="Description" />
              <Button type="primary" style={{ marginTop: '10px' }}>
                Add Comment
              </Button>
            </div>
            <div style={{width:'60%',}}>
              <h5 style={{margin:0}}>Comments</h5>
              <List
                style={{maxHeight:'158px', overflowY:'scroll'}}
                itemLayout="horizontal"
                dataSource={comments}
                renderItem={item => (
                  <List.Item style={{display: 'flex',flexDirection: 'column',}}>
                    <List.Item.Meta
                      avatar={<Avatar src={item.avatar} />}
                      title={<div>
                        <span style={{ marginRight: '8px', fontSize:'12px' }}>{item.name}</span>
                        <i style={{ color: '#888', fontSize: '11px' }}>{item.dateTime}</i>
                      </div>}
                      style={{width:'100%',}}
                    />
                    <div style={{width:'100%',textIndent:'3rem'}}>
                      <b>{item.commentTitle}</b> &nbsp;
                      <i>{item.commentDescription}</i>
                    </div>
                  </List.Item>
                )}
              />
            </div>
          </div>
        </Panel>
      </Collapse>
      <div style={{ overflowX: 'scroll' }}>
        <Table
          className="datatable invoices-table"
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

export default Invoices;
