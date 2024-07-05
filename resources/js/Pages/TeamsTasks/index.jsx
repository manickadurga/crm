import React, { useState, useEffect } from "react";
<<<<<<< HEAD
import { Space, Table, Button, Modal, Input, Select, Tag, DatePicker, Tooltip, Typography } from "antd";
import { EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getTasks } from "../../API";
=======
import { Space, Table, Button, Modal, Input, Select, Tag, DatePicker, Row, Col, Card, Tooltip, Typography,message } from "antd";
import {  EyeOutlined, DeleteOutlined, ArrowLeftOutlined, EditOutlined, PlusOutlined,UnorderedListOutlined, WindowsOutlined,UserOutlined} from "@ant-design/icons";
import { getTasks, getTeamtask } from "../../API";
import axios from "axios";
>>>>>>> 68e4740 (Issue -#35)
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
<<<<<<< HEAD
  {
    title: "Status",
    dataIndex: "status",
    options: ['none','inprogress', 'pending', 'completed']
  }
=======
  // {
  //   title: "Status",
  //   dataIndex: "status",
  //   options: ['none','inprogress', 'pending', 'completed']
  // }
>>>>>>> 68e4740 (Issue -#35)
];

function TeamsTasks() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  const [searchColumn, setSearchColumn] = useState("");
<<<<<<< HEAD

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
=======
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
    getTeamtask(page).then((res) => {
      console.log("res", res);
      setDataSource(res.tasks);
      setTotalRecords(res.pagination.total);

 // Generate columns dynamically
>>>>>>> 68e4740 (Issue -#35)
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
<<<<<<< HEAD
            ) : key === 'paid' || key === 'status' ? (
=======
            ) : key === 'paid' || key === 'invoice_status' ? (
>>>>>>> 68e4740 (Issue -#35)
              <Select
                placeholder={`Filter by ${key}`}
                style={{ width: '100%' }}
                onChange={value => handleColumnSearch(value, key)}
              >
                {selectOptions.find(option => option.dataIndex === key).options.map((option, optionIndex) => (
                  <Option key={optionIndex} value={option}>{option}</Option>
                ))}
              </Select>
<<<<<<< HEAD
            ) : key === 'createdDate' || key === 'dueDate' ? (
=======
            ) : key === 'createdDate' || key === 'duedate' ? (
>>>>>>> 68e4740 (Issue -#35)
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
<<<<<<< HEAD
        render: (text) => {
=======
      //   render: (text) => {
      //     if (key === 'tags') {
      //       return (
      //         <>
      //           {text.map((tag, index) => (
      //             <Tag key={index} className={`badge-${tag}`}>{tag}</Tag>
      //           ))}
      //         </>
      //       );
      //     } else if (key === 'status' || key === 'paid') {
      //       return <Tag className={`badge-${text.toLowerCase()}`}>{text}</Tag>;
      //     } else {
      //       return text;
      //     }
      //   }
      // }));

      render: (text) => {
        if (text) {
>>>>>>> 68e4740 (Issue -#35)
          if (key === 'tags') {
            return (
              <>
                {text.map((tag, index) => (
                  <Tag key={index} className={`badge-${tag}`}>{tag}</Tag>
                ))}
              </>
            );
<<<<<<< HEAD
          } else if (key === 'status' || key === 'paid') {
=======
          } else if (key === 'invoice_status' || key === 'paid') {
>>>>>>> 68e4740 (Issue -#35)
            return <Tag className={`badge-${text.toLowerCase()}`}>{text}</Tag>;
          } else {
            return text;
          }
<<<<<<< HEAD
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

=======
        } else {
          return null; // or handle the case where text is null
        }
      }
        }));
      
>>>>>>> 68e4740 (Issue -#35)
      setColumns(generatedColumns);

      setLoading(false);
    });

<<<<<<< HEAD
  }, []);
=======
  };
>>>>>>> 68e4740 (Issue -#35)

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

<<<<<<< HEAD
    if (searchColumn === 'createdDate' || searchColumn === 'dueDate') {
=======
    if (searchColumn === 'createdDate' || searchColumn === 'duedate') {
>>>>>>> 68e4740 (Issue -#35)
      const dateValue = new Date(value).getTime();
      const searchTextValue = new Date(searchText).getTime();
      return dateValue === searchTextValue;
    } else if (searchColumn === 'tags') {
      return value.some(tag => tag.toLowerCase().includes(searchText.toLowerCase()));
<<<<<<< HEAD
    } else if (searchColumn === 'status' || searchColumn === 'paid') {
=======
    } else if (searchColumn === 'invoice_status' || searchColumn === 'paid') {
>>>>>>> 68e4740 (Issue -#35)
      return value.toLowerCase().includes(searchText.toLowerCase());
    } else {
      return value.toString().toLowerCase().includes(searchText.toLowerCase());
    }
  });

<<<<<<< HEAD
  const showDeleteModal = (record) => {
    console.log('Delete:', record);
    setDeleteModalVisible(true);
=======
  const showDeleteModal = (id) => {
    setTeamTasksIdToDelete(id);
    setIsModalVisible(true);
  };
const handlePageChange = (page) => {
    setCurrentPage(page);
>>>>>>> 68e4740 (Issue -#35)
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

<<<<<<< HEAD
=======
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
  const deleteTeamtask = async () => {
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

  
>>>>>>> 68e4740 (Issue -#35)
  return (
    <Space size={20} direction="vertical" style={{ width: '100%' }}>
      <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Link to="/tasks">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Team's Tasks</b>
        </div>
<<<<<<< HEAD
        <Link to="/tasks/teams/createform"> 
=======
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
          <Link to="/tasks/teams/createform"> 
>>>>>>> 68e4740 (Issue -#35)
        <Button type="primary" htmlType="button" icon={<PlusOutlined />} >
          Create
        </Button>
        </Link>
<<<<<<< HEAD
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

=======
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
  onOk={deleteTeamtask} // Call deleteInvoice on modal confirmation
  onCancel={handleCancel}
>
  <p>Are you sure you want to delete this invoice?</p>
</Modal>
>>>>>>> 68e4740 (Issue -#35)
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

export default TeamsTasks;




















// import React, { useState, useEffect } from "react";
// import { Space, Table, Button, Modal, Collapse, Input, Select, Tag, DatePicker, Tooltip, Typography } from "antd";
// import { EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
// import { getTasks } from "../../API";
// import { Link } from "react-router-dom";
// import TasksForm from "./form";

// const { Panel } = Collapse;
// const { Option } = Select;
// const { RangePicker } = DatePicker;

// const selectOptions = [
//   {
//     title: "Tags",
//     dataIndex: "tags",
//     options: ['none','urgent', 'important', 'pending', 'completed']
//   },
//   {
//     title: "Paid Status",
//     dataIndex: "paid",
//     options: ['none','paid', 'unpaid']
//   },
//   {
//     title: "Status",
//     dataIndex: "status",
//     options: ['none','inprogress', 'pending', 'completed']
//   }
// ];

// function TeamsTasks() {
//   const [loading, setLoading] = useState(false);
//   const [dataSource, setDataSource] = useState([]);
//   const [searchText, setSearchText] = useState("");
//   const [searchColumn, setSearchColumn] = useState("");

//   const [selectedRowKeys, setSelectedRowKeys] = useState([]);
//   const [selectedRows, setSelectedRows] = useState([]);
//   const [deleteModalVisible, setDeleteModalVisible] = useState(false);
//   const [createModalVisible, setCreateModalVisible] = useState(false);

//   const [columns, setColumns] = useState([]);

//   const [comments, setComments] = useState([]);

//   useEffect(() => {
//     setLoading(true);
//     getTasks().then((res) => {
//       console.log("res", res);
//       setDataSource(res.tasks);

//       // Generate columns dynamically
//       const firstRowKeys = Object.keys(res.tasks[0]);
//       const generatedColumns = firstRowKeys.map((key, index) => ({
//         title: key.charAt(0).toUpperCase() + key.slice(1),
//         dataIndex: key,
//         render: (text) => {
//           if (key === 'tags') {
//             return (
//               <>
//                 {text.map((tag, index) => (
//                   <Tag key={index} className={`badge-${tag}`}>{tag}</Tag>
//                 ))}
//               </>
//             );
//           } else if (key === 'status' || key === 'paid') {
//             return <Tag className={`badge-${text.toLowerCase()}`}>{text}</Tag>;
//           } else {
//             return text;
//           }
//         }
//       }));

//       generatedColumns.push({
//         title: 'Action',
//         dataIndex: 'action',
//         fixed: 'right',
//         render: (text, record) => (
//           <Space size="middle">
//             <Link to={`/tasks/view/${record.id}`}><EyeOutlined /></Link>
//             <Link onClick={() => showDeleteModal(record)}><DeleteOutlined /></Link>
//           </Space>
//         ),
//       });

//       setColumns(generatedColumns);

//       setLoading(false);
//     });
//     const commentsData = [
//       {
//         id: 1,
//         avatar: 'https://example.com/avatar1.png',
//         name: 'John Doe',
//         dateTime: '2024-05-15 10:30 AM',
//         commentTitle: 'Great work!',
//         commentDescription: 'This is awesome!',
//       },
//       {
//         id: 2,
//         avatar: 'https://example.com/avatar2.png',
//         name: 'Jane Smith',
//         dateTime: '2024-05-14 02:45 PM',
//         commentTitle: 'Impressive',
//         commentDescription: 'Keep it up!',
//       },
//       {
//         id: 3,
//         avatar: 'https://example.com/avatar1.png',
//         name: 'Tommy',
//         dateTime: '2024-05-12 10:30 AM',
//         commentTitle: 'Great ba!',
//         commentDescription: 'This is awesome!',
//       },
//       {
//         id: 4,
//         avatar: 'https://example.com/avatar2.png',
//         name: 'marc',
//         dateTime: '2024-05-12 02:45 PM',
//         commentTitle: 'Impressive lyy',
//         commentDescription: 'Keep it up!',
//       },
//     ];

//     setComments(commentsData);
//   }, []);

//   const handleDateSearch = (date, dateString, dataIndex) => {
//     setSearchText(dateString);
//     setSearchColumn(dataIndex);
//   };

//   const handleColumnSearch = (value, dataIndex) => {
//     setSearchText(value);
//     setSearchColumn(dataIndex);
//     if (value === 'none') {
//       setSearchText("");
//       setSearchColumn("");
//     }
//   };

//   const filteredData = dataSource.filter((record) => {
//     if (!searchText || !searchColumn) return true;
//     const value = record[searchColumn];
//     if (!value) return false;

//     if (searchColumn === 'invoiceDate' || searchColumn === 'dueDate') {
//       const dateValue = new Date(value).getTime();
//       const searchTextValue = new Date(searchText).getTime();
//       return dateValue === searchTextValue;
//     } else if (searchColumn === 'tags') {
//       return value.some(tag => tag.toLowerCase().includes(searchText.toLowerCase()));
//     } else if (searchColumn === 'status' || searchColumn === 'paid') {
//       return value.toLowerCase().includes(searchText.toLowerCase());
//     } else {
//       return value.toString().toLowerCase().includes(searchText.toLowerCase());
//     }
//   });

//   const showDeleteModal = (record) => {
//     console.log('Delete:', record);
//     setDeleteModalVisible(true);
//   };

//   const handleDelete = () => {
//     console.log('Deleting:', selectedRows);
//     setDeleteModalVisible(false);
//   };

//   const showCreateModal = () => {
//     setCreateModalVisible(true);
//   };

//   const handleCreateCancel = () => {
//     setCreateModalVisible(false);
//   };

//   return (
//     <Space size={20} direction="vertical" style={{ width: '100%' }}>
//       <div style={{ position: 'sticky', display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
//         <div style={{ display: 'flex', alignItems: 'center' }}>
//           <Link to="/tasks">
//             <Button shape="circle" htmlType="button" size="small">
//               <ArrowLeftOutlined />
//             </Button>
//           </Link>
//           <b style={{ fontSize: '18px', marginLeft: '18px' }}>Team's Tasks</b>
//         </div>
//         <Link to="/tasks/teams/createform"> 
//         <Button type="primary" htmlType="button" icon={<PlusOutlined />} >
//           Create
//         </Button>
//         </Link>
//       </div>

//       <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', rowGap: '8px', justifyContent:'space-between' }}>
//         {columns.slice(0, -6).map((column, index) => (
//           <Input
//             key={index}
//             placeholder={`Search ${column.title}`}
//             onChange={e => handleColumnSearch(e.target.value, column.dataIndex)}
//             style={{ maxWidth: '240px' }}
//           />
//         ))}
//         <DatePicker
//           style={{ maxWidth: '240px' }}
//           onChange={(date, dateString) => handleDateSearch(date, dateString, 'createdDate')}
//         />
//         <DatePicker
//           style={{ maxWidth: '240px' }}
//           onChange={(date, dateString) => handleDateSearch(date, dateString, 'dueDate')}
//         />
//         {selectOptions.map((select, index) => (
//           <Select
//             key={index}
//             placeholder={`Filter by ${select.title}`}
//             style={{ maxWidth: '240px' }}
//             onChange={value => handleColumnSearch(value, select.dataIndex)}
//           >
//             {select.options.map((option, optionIndex) => (
//               <Option key={optionIndex} value={option}>{option}</Option>
//             ))}
//           </Select>
//         ))}
//       </div>
//       <div style={{ overflowX: 'scroll' }}>
//         <Table
//           className="datatable tasks-table"
//           loading={loading}
//           columns={columns}
//           dataSource={filteredData}
//           scroll={{ x: true, y: 340 }}
//           pagination={{
//             pageSize: 8,
//           }}
//         />
//         <Modal
//           title="Delete Confirmation"
//           open={deleteModalVisible}
//           onCancel={() => setDeleteModalVisible(false)}
//           onOk={handleDelete}
//           okText="Delete"
//           cancelText="Cancel"
//         >
//           Are you sure you want to delete the selected item(s)?
//         </Modal>

//         <Modal
//           title="Create Task"
//           open={createModalVisible}
//           onCancel={handleCreateCancel}
//           footer={null}
//         >
//           <TasksForm />
//         </Modal>
//       </div>
//     </Space>
//   );
// }

// export default TeamsTasks;