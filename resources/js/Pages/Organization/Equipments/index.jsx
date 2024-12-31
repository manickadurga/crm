import React, { useState, useEffect } from "react";
import { Space, Table, Button, Modal, Input, Select, Tag, DatePicker, Row, Col, Card, Tooltip, Typography,message } from "antd";
import {  EyeOutlined, DeleteOutlined, ArrowLeftOutlined, EditOutlined, PlusOutlined,UnorderedListOutlined, WindowsOutlined,UserOutlined} from "@ant-design/icons";
import { getDataFunction, getTasks,  } from "../../../API";
import axios from "axios";
import { Link } from "react-router-dom";
import EquipmentsForm from "./form";
import { useLocation } from "react-router-dom";
const { Option } = Select;


function Equipments() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [searchColumn, setSearchColumn] = useState("");
  const [selectedTeamTask, setSelectedTeamTask] = useState(null);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedRows, setSelectedRows] = useState([]);
  // const [columns, setColumns] = useState([]);
  const [totalRecords, setTotalRecords] = useState(0);
  const [isModalVisible, setIsModalVisible] = useState(false);

  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [createModalVisible, setCreateModalVisible] = useState(false);
  const [teamTasksIdToDelete,setTeamTasksIdToDelete]=useState(null)
  const [columns, setColumns] = useState([]);


  useEffect(() => {
    fetchTeamtask(currentPage);
  }, [currentPage]);


  const fetchTeamtask = (page) => {
    setLoading(true);
    getDataFunction('teamtasks',page).then((res) => {
      console.log("res", res);
      setDataSource(res.tasks);
      setTotalRecords(res.pagination.total);

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
            ) : key === 'paid' || key === 'invoice_status' ? (
              <Select
                placeholder={`Filter by ${key}`}
                style={{ width: '100%' }}
                onChange={value => handleColumnSearch(value, key)}
              >
                {selectOptions.find(option => option.dataIndex === key).options.map((option, optionIndex) => (
                  <Option key={optionIndex} value={option}>{option}</Option>
                ))}
              </Select>
            ) : key === 'createdDate' || key === 'duedate' ? (
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
        if (text) {
          if (key === 'tags') {
            return (
              <>
                {text.map((tag, index) => (
                  <Tag key={index} className={`badge-${tag}`}>{tag}</Tag>
                ))}
              </>
            );
          } else if (key === 'invoice_status' || key === 'paid') {
            return <Tag className={`badge-${text.toLowerCase()}`}>{text}</Tag>;
          } else {
            return text;
          }
        } else {
          return null; // or handle the case where text is null
        }
      }
        }));
      
      setColumns(generatedColumns);

      setLoading(false);
    });

  };

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

    if (searchColumn === 'createdDate' || searchColumn === 'duedate') {
      const dateValue = new Date(value).getTime();
      const searchTextValue = new Date(searchText).getTime();
      return dateValue === searchTextValue;
    } else if (searchColumn === 'tags') {
      return value.some(tag => tag.toLowerCase().includes(searchText.toLowerCase()));
    } else if (searchColumn === 'invoice_status' || searchColumn === 'paid') {
      return value.toLowerCase().includes(searchText.toLowerCase());
    } else {
      return value.toString().toLowerCase().includes(searchText.toLowerCase());
    }
  });

  const showDeleteModal = (id) => {
    setTeamTasksIdToDelete(id);
    setIsModalVisible(true);
  };
const handlePageChange = (page) => {
    setCurrentPage(page);
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

  const handleCancel = () => {
    setIsModalVisible(false);
    setInvoiceIdToDelete(null);
  };

  const handleRowClick = (record) => {
    setSelectedTeamTask(prevTeamTask => {
      // Toggle selection if clicking on the same invoice
      if (prevTeamTask && prevTeamTask.id === record.id) {
        return null; // Deselect
      } else {
        return record; // Select the new invoice
      }
    });
  };
  const deleteEquipments = async () => {
    try {
      if (!teamTasksIdToDelete) {
        console.error('Invoice ID is undefined');
        return;
      }
      await axios.delete(`http://127.0.0.1:8000/teamtasks/${teamTasksIdToDelete}`);
      console.log('Teamtaks deleted');
      setDataSource(dataSource.filter(teamTasks => teamTasks.id !== teamTasksIdToDelete)); // Update state
      
      message.success('Teamtask Deleted Successfully')
      
      setIsModalVisible(false);
        currentPage();
      // navigate('/tea'); // Redirect to the invoices page after deletion if needed
    } catch (error) {
      console.error('There was an error deleting the teamTasks:', error);
    }
  };
  

  const renderCardsComponent = (record) => (
    <Row gutter={16}>
      {dataSource.map((teamTasks) => (
        <Col key={teamTasks.id} span={8}>
          <Card
            title={`TeamTask ${teamTasks.id}`}
            onClick={() => handleRowClick(teamTasks)}
            style={{
              cursor: 'pointer',
              backgroundColor: selectedTeamTask && selectedTeamTask.id === teamTasks.id ? '#f0f0f0' : 'white'
            }}
          >
            {Object.keys(teamTasks).map((key) => (
              <p key={key}>
                <strong>{key}:</strong> {teamTasks[key]}
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
          <Link to="/tasks">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Equipments</b>
        </div>
        <div>
        {selectedTeamTask && (
    <>
      <Link to={`/invoices/view/${selectedTeamTask.id}`}>
        <Button type="link" style={{ marginRight: '8px', border: '1px solid #ccc', background: 'white' }}>
          <EyeOutlined /> View
        </Button>
      </Link>
      <Link to={`/tasks/teams/editfrom/${selectedTeamTask.id}`}>
        <Button type="link" style={{ marginRight: '8px', border: '1px solid #ccc', background: 'white' }}>
          <EditOutlined /> Edit
        </Button>
      
      </Link>
      <Button 
        // type="danger" 
        onClick={() => showDeleteModal(selectedTeamTask.id)} 
        // onClick={() => showDeleteModal(selectedInvoice.id)} 
        style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
         <DeleteOutlined />
      </Button>
      </>
  )}
          <Link to="/equipments/createform"> 
        <Button type="primary" htmlType="button" icon={<PlusOutlined />} >
          Create
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

      <div style={{ overflowX: 'scroll' }}>
      {viewMode === 'table' ? ( 
        <Table
        className="datatable tasks-table"
        loading={loading}
        columns={columns}
        dataSource={filteredData}
        scroll={{ x: true, y: 340 }}
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
            backgroundColor: selectedTeamTask && selectedTeamTask.id === record.id ? '#f0f0f0' : 'white',
          },
         })}
      />
      ):(renderCardsComponent())
}
        
 <Modal
   title="Confirm Deletion"
   visible={isModalVisible}
   onOk={deleteEquipments} // Call deleteInvoice on modal confirmation
   onCancel={handleCancel}
  >
  <p>Are you sure you want to delete this invoice?</p>
</Modal>
        <Modal
          title="Create Task"
          open={createModalVisible}
          onCancel={handleCreateCancel}
          footer={null}
        >
          <EquipmentsForm />
        </Modal>
      </div>
    </Space>
  );
}

export default Equipments;