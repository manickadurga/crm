import React, { useState, useEffect } from "react";
<<<<<<< HEAD
import { Space, Table, Button, Modal, Input, Select, Tag, DatePicker } from "antd";
=======
import { Space, Table, Button, Modal, Collapse, Input, Select, Tag, DatePicker, Tooltip, Typography } from "antd";
>>>>>>> 68e4740 (Issue -#35)
import { EyeOutlined, DeleteOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
import { getTasks } from "../../API";
import { Link } from "react-router-dom";
import TasksForm from "./form";

<<<<<<< HEAD
const { Option } = Select;
=======
const { Panel } = Collapse;
const { Option } = Select;
const { RangePicker } = DatePicker;
>>>>>>> 68e4740 (Issue -#35)

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

<<<<<<< HEAD
=======
  const [comments, setComments] = useState([]);

>>>>>>> 68e4740 (Issue -#35)
  useEffect(() => {
    setLoading(true);
    getTasks().then((res) => {
      console.log("res", res);
      setDataSource(res.tasks);

      // Generate columns dynamically
      const firstRowKeys = Object.keys(res.tasks[0]);
      const generatedColumns = firstRowKeys.map((key, index) => ({
<<<<<<< HEAD
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
=======
        title: key.charAt(0).toUpperCase() + key.slice(1),
>>>>>>> 68e4740 (Issue -#35)
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
<<<<<<< HEAD
=======
    const commentsData = [
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

    setComments(commentsData);
>>>>>>> 68e4740 (Issue -#35)
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

<<<<<<< HEAD
    if (searchColumn === 'createdDate' || searchColumn === 'dueDate') {
=======
    if (searchColumn === 'invoiceDate' || searchColumn === 'dueDate') {
>>>>>>> 68e4740 (Issue -#35)
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

<<<<<<< HEAD
=======
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', rowGap: '8px', justifyContent:'space-between' }}>
        {columns.slice(0, -6).map((column, index) => (
          <Input
            key={index}
            placeholder={`Search ${column.title}`}
            onChange={e => handleColumnSearch(e.target.value, column.dataIndex)}
            style={{ maxWidth: '240px' }}
          />
        ))}
        <DatePicker
          style={{ maxWidth: '240px' }}
          onChange={(date, dateString) => handleDateSearch(date, dateString, 'createdDate')}
        />
        <DatePicker
          style={{ maxWidth: '240px' }}
          onChange={(date, dateString) => handleDateSearch(date, dateString, 'dueDate')}
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
>>>>>>> 68e4740 (Issue -#35)
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
<<<<<<< HEAD
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
=======
        /><Modal
        title="Delete Confirmation"
        visible={deleteModalVisible}
        onCancel={() => setDeleteModalVisible(false)}
        onOk={handleDelete}
        okText="Delete"
        cancelText="Cancel"
      >
        Are you sure you want to delete the selected item(s)?
      </Modal>
      <Modal
        title="View Details"
        visible={viewModalVisible}
        onCancel={() => setViewModalVisible(false)}
        footer={null}
      >
        <div>
          <strong>Tasks Number:</strong> {selectedRecord && selectedRecord.tasksnumber}
        </div>
        <div>
          <strong>Projects:</strong> {selectedRecord && selectedRecord.projects && selectedRecord.projects.map(project => (
            <div key={project.id}>
              <div><strong>Name:</strong> {project.name}</div>
              <div><strong>Description:</strong> {project.description}</div>
              {/* Add more project fields as needed */}
            </div>
          ))}
        </div>
        <div>
          <strong>Status:</strong> {selectedRecord && selectedRecord.status}
        </div>
        <div>
          <strong>Teams:</strong> {selectedRecord && selectedRecord.teams}
        </div>
        <div>
          <strong>Title:</strong> {selectedRecord && selectedRecord.title}
        </div>
        <div>
          <strong>Priority:</strong> {selectedRecord && selectedRecord.priority}
        </div>
        <div>
          <strong>Size:</strong> {selectedRecord && selectedRecord.size}
        </div>
        <div>
          <strong>Tags:</strong> {selectedRecord && selectedRecord.tags && selectedRecord.tags.map(tag => (
            <span key={tag}>{tag}, </span>
          ))}
        </div>
        <div>
          <strong>Due Date:</strong> {selectedRecord && selectedRecord.duedate}
        </div>
        <div>
          <strong>Estimate Days:</strong> {selectedRecord && selectedRecord.estimate_days}
        </div>
        <div>
          <strong>Estimate Hours:</strong> {selectedRecord && selectedRecord.estimate_hours}
        </div>
        <div>
          <strong>Estimate Minutes:</strong> {selectedRecord && selectedRecord.estimate_minutes}
        </div>
      </Modal>
      
        <TasksForm/>
>>>>>>> 68e4740 (Issue -#35)
      </div>
    </Space>
  );
}

<<<<<<< HEAD
export default Tasks;











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

// function Tasks() {
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
//           <Link to="/">
//             <Button shape="circle" htmlType="button" size="small">
//               <ArrowLeftOutlined />
//             </Button>
//           </Link>
//           <b style={{ fontSize: '18px', marginLeft: '18px' }}>Tasks</b>
//         </div>
//         <Link to="/tasks/createform"> 
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

// export default Tasks;
=======
export default Tasks;
>>>>>>> 68e4740 (Issue -#35)
