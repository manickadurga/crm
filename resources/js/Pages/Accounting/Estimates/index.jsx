
import React, { useState, useEffect } from "react";
import { Space,message, Table, Button, Modal, Collapse, Input, Select, Tag, DatePicker, List, Avatar, Row, Col, Card } from "antd";
import { EyeOutlined, DeleteOutlined, ArrowLeftOutlined, EditOutlined, PlusOutlined,UnorderedListOutlined, WindowsOutlined, } from "@ant-design/icons";
// import { getGoals } from "../../API";
import { getGoals } from "../../../API";
import { Link,useParams,useNavigate } from "react-router-dom";
import { getEstimates } from "../../../API/estimatesApi";
import axios from "axios";
import { DownOutlined, UserOutlined } from '@ant-design/icons';
import { Dropdown, Tooltip } from 'antd';


const { Panel } = Collapse;
const { Option } = Select;
const { RangePicker } = DatePicker;

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

function Estimates() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [searchColumn, setSearchColumn] = useState("");
  const [selectedInvoice, setSelectedInvoice] = useState(null);
  // const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [estimateIdToDelete, setEstimateIdToDelete] = useState(null);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
  const [columns, setColumns] = useState([]);
  const [comments, setComments] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);  
  const [totalRecords, setTotalRecords] = useState(0);
  const [activeRowId, setActiveRowId] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
    const { id } = useParams(); // Get invoiceId from URL parameters
    const navigate = useNavigate(); // To navigate programmatically
  

 
 
  useEffect(() => {
    fetchInvoices(currentPage);
  }, [currentPage]);

  const fetchInvoices = (page) => {
    setLoading(true);
    getInvoices(page)
      .then(res => {
        console.log("Fetched invoices:", res);

        if (res.invoices && res.invoices.length > 0) {
          setDataSource(res.invoices);
          setTotalRecords(res.pagination.total);

          const firstInvoice = res.invoices[0];
          const generatedColumns = Object.keys(firstInvoice).map(key => ({
            title: key.charAt(0).toUpperCase() + key.slice(1),
            dataIndex: key,
            key: key,
            render: (text, record) => {
              if (key === 'tags') {
                return (
                  <>
                    {record.tags.map(tag => (
                      <Tag key={tag} className={`badge-${tag}`}>{tag}</Tag>
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

          setColumns(generatedColumns);
        } else {
          console.log("No invoices found.");
          setDataSource([]);
          setColumns([]);
        }

        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching invoices:', error);
        setLoading(false);
      });
  };

  const showDeleteModal = (id) => {
    setEstimateIdToDelete(id);
    setIsModalVisible(true);
  };

  const handleCancel = () => {
    setIsModalVisible(false);
    setEstimateIdToDelete(null);
  };
  const showModal = () => {
    setIsModalOpen(true);
  };
  const handleOk = () => {
    setIsModalOpen(false);
  };
  const downloadhandleCancel = () => {
    setIsModalOpen(false);
  };
  
const deleteInvoice = async () => {
  try {
    if (!estimateIdToDelete) {
      console.error('Invoice ID is undefined');
      return;
    }
    await axios.delete(`http://127.0.0.1:8000/estimate/${estimateIdToDelete}`);
    console.log('Invoice deleted');
    setDataSource(dataSource.filter(invoice => invoice.id !== estimateIdToDelete)); // Update state
    
    message.success('Invoice Deleted Successfully')
    
    setIsModalVisible(false);
    fetchInvoices(currentPage);
    navigate('/invoices'); // Redirect to the invoices page after deletion if needed
  } catch (error) {
    console.error('There was an error deleting the invoice:', error);
  }
};

const handleButtonClick = (e) => {
  message.info('Click on left button.');
  console.log('click left button', e);
};
const handleMenuClick = (e) => {
  message.info('Click on menu item.');
  console.log('click', e);
};
  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

 
  const renderColumns = () => {
    return columns.length > 0 ? columns : [{
      title: 'Invoice Number',
      dataIndex: 'invoicenumber',
      key: 'invoicenumber',
    }];
  };
 
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
  const items = [
    {
      label: '1st menu item',
      key: '1',
      // icon: <UserOutlined />,
    },
    {
      label: '2nd menu item',
      key: '2',
      icon: <UserOutlined />,
    },
    {
      label: '3rd menu item',
      key: '3',
      icon: <UserOutlined />,
      danger: true,
    },
    {
      label: '4rd menu item',
      key: '4',
      icon: <UserOutlined />,
      danger: true,
      disabled: true,
    },
  ];
  const menuProps = {
    items,
    onClick: handleMenuClick,
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

 
  const handleRowClick = (record) => {
    setSelectedInvoice(prevInvoice => {
      // Toggle selection if clicking on the same invoice
      if (prevInvoice && prevInvoice.id === record.id) {
        return null; // Deselect
      } else {
        return record; // Select the new invoice
      }
    });
  };
  
  const renderCardsComponent = (record) => (
    <Row gutter={16}>
      {dataSource.map((invoice) => (
        <Col key={invoice.id} span={8}>
          <Card
            title={`Invoice ${invoice.id}`}
            onClick={() => handleRowClick(invoice)}
            style={{
              cursor: 'pointer',
              // backgroundColor:'red',
              backgroundColor: selectedInvoice && selectedInvoice.id === invoice.id ? '#f0f0f0' : 'white'
            }}
          >
            {Object.keys(invoice).map((key) => (
              
              <p key={key} style={{padding:'2px',background:'red'}}>
                <strong>{key}:</strong> {invoice[key]}
              </p>
            ))}
          </Card>
        </Col>
      ))}
    </Row>
  );


  return (
    <Space size={20} direction="vertical" style={{ width: '-webkit-fill-available' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Invoices</b>
        </div>
        <div>
          {selectedInvoice && (
            <Space size="middle">
             <Link to={`/invoices/view/${selectedInvoice.id}`}>
        <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
          <EyeOutlined />
        </Button>
      </Link>
      <Link to={`/invoices/edit/${selectedInvoice.id}`}>
        <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
          <EditOutlined />
        </Button>
      </Link>
      {/* <Link to={`/invoices/edit/${selectedInvoice.id}`}> */}
        <Button
        onClick={() => showModal(selectedInvoice.id)} 
        type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
          Download
        </Button>
      {/* </Link> */}
       <Link to={`/invoices/payment/${selectedInvoice.id}`}>
        <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
          Payment
        </Button>
      </Link>
      <Button 
        // type="danger" 
        onClick={() => showDeleteModal(selectedInvoice.id)} 
        style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
         <DeleteOutlined />
      </Button>

      {/* <Dropdown.Button menu={menuProps} onClick={handleButtonClick}>
    </Dropdown.Button> */}

     </Space> 
          )}
          <Link to="/invoices/createform">
            <Button type="primary" htmlType="button" icon={<PlusOutlined />}>
              Add
            </Button>
          </Link>
          <Button
            type={viewMode === 'table' ? 'primary' : 'default'}
            onClick={() => setViewMode('table')}
          >
            <UnorderedListOutlined/> 
          </Button>
          <Button
            type={viewMode === 'card' ? 'primary' : 'default'}
            onClick={() => setViewMode('card')}
          >
            <WindowsOutlined/>
          </Button>
        </div>
      </div>
      <Collapse bordered={false} style={{ backgroundColor: '#fff' }}>
        <Panel header="Advanced Search" key="1">
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', rowGap: '8px' }}>
            {columns.map((column, index) => (
              <Input
                key={index}
                placeholder={`Search ${column.title}`}
                onChange={e => handleColumnSearch(e.target.value, column.dataIndex)}
                style={{ maxWidth: '240px' }}
              />
            ))}
            <RangePicker
              style={{ maxWidth: '240px' }}
              onChange={(dates, dateStrings) => handleDateRangeSearch(dates, dateStrings, 'invoiceDate')}
            />
            <RangePicker
              style={{ maxWidth: '240px' }}
              onChange={(dates, dateStrings) => handleDateRangeSearch(dates, dateStrings, 'dueDate')}
            />
            {selectOptions.map((select, index) => (
              <Select
                key={index}
                placeholder={`Filter by ${select.title}`}
                style={{ maxWidth: '240px' }}
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
          <div style={{ display: 'flex', gap: '16px' }}>
            <div style={{ width: '40%' }}>
              {/* Left side: Title and Description Input */}
              <Input type='text' placeholder="Title" style={{ marginBottom: '10px' }} />
              <Input.TextArea placeholder="Description" />
              <Button type="primary" style={{ marginTop: '10px' }}>
                Add Comment
              </Button>
            </div>
            <div style={{ width: '60%' }}>
              <h5 style={{ margin: 0 }}>Comments</h5>
              <List
                style={{ maxHeight: '158px', overflowY: 'scroll' }}
                itemLayout="horizontal"
                dataSource={comments}
                renderItem={item => (
                  <List.Item style={{ display: 'flex', flexDirection: 'column' }}>
                    <List.Item.Meta
                      avatar={<Avatar src={item.avatar} />}
                      title={<div>
                        <span style={{ marginRight: '8px', fontSize: '12px' }}>{item.name}</span>
                        <i style={{ color: '#888', fontSize: '11px' }}>{item.dateTime}</i>
                      </div>}
                      style={{ width: '100%' }}
                    />
                    <div style={{ width: '100%', textIndent: '3rem' }}>
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
      <div
     style={{ overflowX: 'scroll' }}
      >
        {viewMode === 'table' ? (
 <Table
 className="datatable invoices-table"
 loading={loading}
 columns={renderColumns()}
 dataSource={filteredData}
 pagination={{
   current: currentPage,
   pageSize: 10,
   total: totalRecords,
   onChange: handlePageChange,
 }}
 onRow={(record) => ({
  onClick: () => handleRowClick(record),
  style: {
    cursor: 'pointer',
    backgroundColor: selectedInvoice && selectedInvoice.id === record.id ? '#f0f0f0' : 'white',
  },
})}
/>
        ) : (
          renderCardsComponent()
        )}
        <Modal title="Basic Modal" open={isModalOpen} onOk={handleOk} onCancel={downloadhandleCancel}>
        <p>Some contents...</p>
        <p>Some contents...</p>
        <p>Some contents...</p>
      </Modal>
     
<Modal
  title="Confirm Deletion"
  visible={isModalVisible}
  onOk={deleteInvoice} // Call deleteInvoice on modal confirmation
  onCancel={handleCancel}
>
  <p>Are you sure you want to delete this invoice?</p>
</Modal>

      </div>
    </Space>
  );
}

export default Estimates;

