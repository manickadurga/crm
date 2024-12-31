import React, { useState,useEffect } from 'react';
import { Pagination,Button, Col, Row, Table, Input, Space, Tabs } from 'antd';
import axios from 'axios';
import { PlusOutlined, SearchOutlined,SettingOutlined, ClockCircleOutlined, MenuOutlined,FolderOutlined, EditOutlined,
  DeleteOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { getDataFunction } from '../../API';


// Destructure TabPane from Tabs for proper usage
const { TabPane } = Tabs;

const WorkFlow = () => {
  const navigate = useNavigate();
  const [searchText, setSearchText] = useState('');
  const [workFlow,setWorkFlow] = useState([])
 console.log("WorkFlow",workFlow)

  const onChange = (page) => {
    console.log(page);
  };

  useEffect(() => {
    getDataFunction('workflows')
        .then((res) => {
          setWorkFlow(res|| []);
          })
        .catch((error) => {
            console.error("Error fetching form fields:", error);
        });
}, []);

 const handleEdit = (workFlowId) => {
  console.log('workflowId:', workFlowId); // Check if it's coming as expected
  if (!workFlowId) {
    console.error('workflowId is not defined or missing');
  } else {
    navigate(`/nestworkflow/${workFlowId}`); // This will redirect to the correct URL
  }
};

const handleDelete = (record) => {
    console.log("Delete clicked for:", record.workflow_name);
    // Remove the selected workflow from the state
    setWorkFlow((prevWorkFlow) =>
      prevWorkFlow.filter((workflow) => workflow.id !== record.id)
    );
  };

const columns = [
   {
      title: "WorkFlow Id",
      dataIndex: "id",
      key: "id",
    },
    {
      title: "WorkFlow Name",
      dataIndex: "workflow_name",
      key: "workflow_name",
    },
    {
      title: "Trigger Id",
      dataIndex: "trigger_id",
      key: "trigger_id",
    },
   {
    title: "Action Id",
    dataIndex: "actions_id",
    key: "actions_id",
    render: (actions_id) => {
    try {
      // Parse the actions_id string into an array
      const ids = JSON.parse(actions_id);
      return ids.join(", "); // Convert the array into a comma-separated string
    } catch (error) {
      console.error("Error parsing actions_id:", error);
      return "Invalid Data"; // Handle invalid JSON gracefully
    }
  },
},

    // {
    //   title: "Last Updated",
    //   dataIndex: "created_at",
    //   key: "created_at",
    //   render: (created_at) => new Date(created_at).toLocaleString(), // Format the date for display
    // },
    {
      title: "Actions",
      key: "actions",
      render: (record) => (
        <>
          <EditOutlined
         style={{ marginRight: 16, color: "#1890ff" }}
         onClick={() => {
         console.log('Record:', record); // Log the record to see the structure and check workflow_id
         handleEdit(record.id); // Pass workflow_id
      }}
   />

       <DeleteOutlined style={{ color: "red", cursor: "pointer" }} onClick={() => handleDelete(record)} />
          </>
      ),
      
    },
    
  ];

  
  const handleSearch = (e) => {
    setSearchText(e.target.value);
  };

  const filteredWorkFlow = workFlow.filter((item) =>
    item.workflow_name.toLowerCase().includes(searchText.toLowerCase())
  );

  return (
    <>
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>  
        <Col>
          <Tabs defaultActiveKey="1">
            <TabPane tab="Automation" key="1" />
            <TabPane tab="Workflows" key="2" />
            <TabPane tab="Content AI" key="3"/>
            <Tabs.TabPane
  tab={
    <span>
      <SettingOutlined style={{ marginRight: 8 }} />
      Global Settings
    </span>
  }
  key="4"
/>
     </Tabs>
        </Col>
      </Row>

     <Row style={{ alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
        <Col>
          <h2 style={{ fontWeight: 300, fontFamily: "'Poppins', sans-serif" }}>
            WorkFlow List
          </h2>
        </Col>
        <Col>
    <Space>
      <Button
        icon={<FolderOutlined />}
      >
        Create Folder
      </Button>
      <Button 
        type="primary" 
        icon={<PlusOutlined />} 
        onClick={() => navigate('/nestworkflow')}
      >
        Create New Workflow
      </Button>
    </Space>
  </Col>
      </Row>
      <Row style={{ alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
  {/* Tabs on the left */}
  <Col>
    <Tabs defaultActiveKey="1" type="card">
      <Tabs.TabPane tab="All Workflows" key="1" />
      <Tabs.TabPane tab="Needs Review" key="2" />
      <Tabs.TabPane tab="Deleted" key="3" />
      <Tabs.TabPane 
        tab={
          <span>
            Smart List <PlusOutlined />
          </span>
        } 
        key="4" 
      />
    </Tabs>
  </Col>

  {/* Button on the right */}
  <Col>
    <Button icon={<SettingOutlined />} type="default">
      Customize List
    </Button>
  </Col>
</Row>
{/* Advanced Filters and other buttons */}
      <Row style={{ alignItems: 'center', marginBottom: '16px' }}>
  {/* Advanced Filters Button */}
  <Col span={8}>
    <Button 
      type="default" 
      icon={<SearchOutlined />} 
      onClick={() => {}} 
      style={{ marginRight: 16, borderRadius: 5 }}
    >
      Advanced Filters
    </Button>
  </Col>
    {/* Buttons with Menu and Clock Icons */}
  <Col span={12} style={{ textAlign: 'right' }}>
    <Space>
      <Button icon={<ClockCircleOutlined />} />
      <Button icon={<ClockCircleOutlined />} />
      <Button icon={<MenuOutlined />} />
    </Space>
  </Col>

  {/* Search Box */}
  <Col span={4} style={{ paddingLeft: '8px' }}>
    <Input 
      placeholder="Search Workflow" 
      value={searchText} 
      onChange={handleSearch} 
      style={{ width: '100%' }}
      prefix={<SearchOutlined />}
    />
  </Col>
</Row>
<Row>
  <Col>
  <h1>Home</h1>
  </Col>
</Row>
 {/* Table */}
      <Table
        columns={columns}
        dataSource={filteredWorkFlow}  // Use the filtered workflow state
        rowKey="id"
        pagination={false}
      />
       <Row gutter={[16, 16]}>
        {/* Table rows go here */}
      </Row>

      {/* Pagination */}
      <Row justify="end" style={{ marginTop: '16px' }}>
        <Col>
          <Pagination
            showQuickJumper
            defaultCurrent={2}
            total={500}
            onChange={onChange}
          />
        </Col>
      </Row>
    </>
    );
    
};

export default WorkFlow;

// import React, { useEffect, useRef, useState } from 'react';
// import { useNavigate,useParams } from 'react-router-dom';
// import {
//   ReactFlow,
//   useNodesState,
//   useEdgesState,
//   addEdge,
//   useReactFlow,
//   ReactFlowProvider,
//   Handle,
// } from '@xyflow/react';
// import ReactQuill from 'react-quill';
// import { Switch, Divider, Drawer, Button, Select, Input, Dropdown, Menu,Row, Col, Space, Tabs, Form, message} from 'antd';
// import {getDataFunction} from '../../API/index';
// import { UnorderedListOutlined,PlusOutlined, ArrowLeftOutlined,DeleteOutlined ,MenuOutlined ,DownOutlined, MailOutlined, PhoneOutlined, MessageOutlined, WhatsAppOutlined, UploadOutlined
//   } from '@ant-design/icons';
// import '@xyflow/react/dist/style.css';
// import './Index.css';
// import axios from 'axios';

// const { Option } = Select;
// const initialNodes = [
//   {
//     id: '0',
//     type: 'addTrigger', // Custom node type for "Add New Trigger"
//     data: { label: ' Add New Trigger' },
//     position: { x: 0, y: 50 },
//     style: {
//       display: 'flex',
//       justifyContent: 'center', // Centers the text horizontally
//       alignItems: 'center', // Centers the text vertically
//       backgroundColor: '#e7f3ff', // White background inside the node box
//       border: "1px dashed blue", // Blue border around the node
//       borderRadius: '5px', // Rounded corners
//       padding: '16px 24px', // Adequate padding inside the node
//       fontSize: '14px', // Adjust font size for readability
//       fontWeight: '600', // Slightly bolder text for emphasis
//       color: 'black', // Black text color
//       boxShadow: '0 4px 12px rgba(0, 0, 0, 0.2)', // Subtle shadow for depth
//       cursor: 'pointer', // Pointer cursor for interactivity
//       transition: 'all 0.3s ease', // Smooth transition for hover effects
//       width: '200px', // Fixed width for the node
//       height: '50px', // Set a fixed height for uniformity
//       textAlign: 'center', // Center the text inside the node
//     },
//   },
// ];

// let id = 1;
// const getId = () => `${id++}`;
// const nodeOrigin = [0.5, 0];

// const AddNodeOnEdgeDrop = () => {
//   const reactFlowWrapper = useRef(null);
//   const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
//   const [edges, setEdges, onEdgesChange] = useEdgesState([]);
//   const [nodeData, setNodeData] = useState([]);
//   const [triggerVisible, settrVisible] = useState(false);
//   const [isDataLoaded, setIsDataLoaded] = useState(false);
//   const [form] = Form.useForm(); // Initialize the form instance
//   const [nodeToEdit, setNodeToEdit] = useState(null);
//   const { screenToFlowPosition } = useReactFlow();
//   const [customer, setCustomer] = useState([]);
//   const [editDrawerVisible, setEditDrawerVisible] = useState(false);
//   const { zoomIn, zoomOut } = useReactFlow();
//   const [drawerVisible, setDrawerVisible] = useState(false);
//   const [isDrawerOpen, setIsDrawerOpen] = useState(false);
//   const [triggerData, setTriggerData] = useState([]);
//   const [actionData, setActionData] = useState([]);
//   const [position, setPosition] = useState({ x: 0, y: 0 });
//   const [triggerState, setTriggerState] = useState({
//     workflowTrigger: '',
//     workflowTriggerName: '',
//     filters: [],
//   });
  
//   const [selectedCustomer, setSelectedCustomer] = useState('');
//   const [fileName,setFileName] = useState('')
//   const [selectedOperator, setSelectedOperator] = useState('');
//   const [isFirstNode, setIsFirstNode] = useState(true);
//   const [actionId,setActionId] = useState([]);
//   const { workflowId } = useParams(); 
//   const navigate = useNavigate(); // React Router's navigate hook
//   const [actionDrawerVisible, setActionDrawerVisible] = useState(false);
//   const [emailActionDrawerVisible, setEmailActionDrawerVisible] = useState(false);
//   const [editingNodeId, setEditingNodeId] = useState(); 
//   const [triggerId,setTriggerId] = useState()
//   const [triggerDrawerVisible, setTriggerDrawerVisible] = useState(false);
//   const [selectedNodeData, setSelectedNodeData] = useState(null);
//   const [saving, setSaving] = useState(false); // Track the save state
//   const [formData, setFormData] = useState({
//     action_name: '',
//     from_name: '',
//     from_email: '',
//     subject: '',
//     template: 'Email', // Default value for template
//     content: '',
//     testEmail: '',
//     attachments:'',
//   });
//   const [editAction,setEditAction] = useState({
//     id:'',
//     action_name: '',
//     from_name: '',
//     from_email: '',
//     subject: '',
//     template: 'Email', // Default value for template
//     content: '',
//     testEmail: '',
//     attachments:'',
//   })
//   const [filePath, setFilePath] = useState('');
//   const [testEmail, setTestEmail] = useState(formData.testEmail || '');

//  const handleDeleteNode = (nodeId) => {
//     console.log(`Attempting to delete node with ID: ${nodeId}`); // Log the node ID
//     setNodes((prevNodes) => prevNodes.filter((node) => node.id !== String(nodeId)));
//     console.log(`Node with ID ${nodeId} deleted`);
//   };

//   // Function to handle opening the drawer with existing node data
//   const handleNodeClick = (formData, actionId) => {
//     console.log('edit node Id',);
//     //formdata - id:key kku setActionID value ya pass panu
//     const edit ={...formData,id:actionId}
//     console.log("Edit",edit);
     
//     setNodeToEdit(actionId);  // Store the node ID we are editing
//     setFormData(formData);   // Set form data to the current node's data
//     setNodeData(formData)   
//     setIsDrawerOpen(true);   // Open the drawer
//   };

// useEffect(() => {
//     getDataFunction('customers')
//         .then((res) => {
//             setCustomer(res.customers || []);
//         })
//         .catch((error) => {
//             console.error("Error fetching form fields:", error);
//         });
// }, []);

// useEffect(() => {
//   if (workflowId) {
//     axios
//       .get(`http://127.0.0.1:8000/api/workflows/${workflowId}`)
//       .then((response) => {
//         const data = response.data;

//         // Check the structure of the response
//         console.log("Workflow Data:", data);

//         const triggerIds = data.trigger_id || [];
//         let actionIds = data.actions_id;

//         // Convert actions_id to integers
//         try {
//           actionIds = JSON.parse(actionIds); // Parse the string as JSON
//           if (!Array.isArray(actionIds)) {
//             actionIds = [actionIds]; // Ensure it's an array
//           }
//           actionIds = actionIds.map((id) => parseInt(id, 10)); // Convert each ID to an integer
//         } catch (error) {
//           console.error("Error parsing actions_id:", error);
//           actionIds = []; // Default to an empty array if parsing fails
//         }

//         console.log("actionIds:", actionIds);
//         console.log("triggerIds:", triggerIds);

//         // Fetch Trigger Data
//         if (triggerIds) {
//           axios
//             .get(`http://127.0.0.1:8000/api/triggers/${triggerIds}`)
//             .then((response) => {
//               const Tdata = response.data;
//               console.log("Trigger Data:", Tdata);

//               if (Tdata) {
//                 // const tridata = {
//                 //  workflowTrigger: data.trigger_name,
//                 //  workflowTriggerName: data.trigger_name,
//                 //  filters: data.filters,
//                 //             }
//                 setTriggerState(Tdata);  
//                 console.log("Tdata",Tdata)
//                 handleSaveTrigger(); // Pass the data to handleSaveTrigger
//               }
//             })
//             .catch((error) => {
//               console.error(`Error fetching trigger ID ${triggerIds}:`, error);
//             });
//         }

//         // Fetch Action Data
//         if (actionIds) {
//           axios
//             .get(`http://127.0.0.1:8000/api/actions/${actionIds}`)
//             .then((response) => {
//               const Adata = response.data;
//               console.log("Actions Data:", Adata);
//               if (Adata) {
//                 // Perform actions with action data
//                 setFormData(Adata)
//                 console.log("Adata",Adata)
//                 handleSaveAction(); // Pass the data to handleSaveAction
//               }
//             })
//             .catch((error) => {
//               console.error(`Error fetching action IDs ${actionIds}:`, error);
//             });
//         }
//       })
//       .catch((error) => {
//         console.error("Error fetching workflow data:", error);
//       });
//   }
// }, [workflowId]);

// useEffect(() => {
//   // Loop over all nodes to access their positions after render
//   nodes.forEach((node) => {
//     const nodeElement = document.getElementById(node.id); // Get the DOM element by its ID
//     if (nodeElement) {
//       const rect = nodeElement.getBoundingClientRect(); // Get the bounding box of the node
//       console.log(`Node Position After Render (ID: ${node.id}):`, rect);
//     }
//   });
// }, [nodes]); // This will run every time the nodes array is updated

// const onNodeDragStop = (node) => {
//   // Reset the position back to the fixed one after drag
//   node.position = { x: 200, y: 100 };
//   setNodes((prevNodes) =>
//     prevNodes.map((n) =>
//       n.id === node.id ? { ...n, position: node.position } : n
//     )
//   );
// };

// // Function to handle saving edited data back to the node
//   const onCanvasClick = (event) => {
//     const rect = reactFlowWrapper.current.getBoundingClientRect();
//     const x = event.clientX - rect.left;
//     const y = event.clientY - rect.top;
//     setPosition({ x, y });
//   };

//   const menu = (nodeId) => (
//   <Menu>
//       <Menu.Item>
//       <Button key="delete"  
//       onClick={() => handleDeleteNode(nodeId)}
//       style={{
//         borderColor: 'red', 
//         borderStyle: 'solid', 
//         borderWidth: '1px',
//        color: 'red'  // Optional: Change the text color to red as well
//       }}>
//         Delete
//       </Button>
//       </Menu.Item>
//       <Menu.Item>
//       <Button
//         key="edit"
//         size="small"
//         onClick={() => handleTriggerEditNode(nodeId)}
//         style={{
//           position: 'relative',
//           display: 'block',
//           marginTop: '8px',
//           borderColor: 'blue', 
//           borderStyle: 'solid', 
//           borderWidth: '1px',
//           width:'70px',
//           color: 'blue'
//       }}
//       >
//         Edit
//       </Button>
//     </Menu.Item>
//    </Menu> 
//   );

//   const handlePlusClick = () => {
//     setDrawerVisible(true);
//   };

//   const triggerClose = () => {
//     setDrawerVisible(false);
//   };

//   const addNewTriggerNode = nodes.find(node => node.id === 'add-new-trigger'); // Replace with your actual trigger node ID
//   const triggerNodePosition = addNewTriggerNode ? addNewTriggerNode.position : { x: 0, y: 0 }; // Default position if not found

// const resetForm = () => {
//   setFormData({
//     action_name: 'Email',
//     from_name: '',
//     from_email: '',
//     subject: '',
//     content: '',
//     testEmail: '',
//     attachments: [],
//     template: 'email', // Default template if needed
//   });

//   setTimeout(() => {
//     form.resetFields(); // Reset fields with a slight delay
//     console.log("Fields reset");
//   }, 0);
// };
// const [content, setContent] = useState(formData.content || '');

// const offsetY = 160; // Adjust this value to change the distance below the trigger node
// const offsetX = 40; // No horizontal offset; adjust this for any horizontal alignment changes

// const handleSaveAction = () => {
//   if (saving) return; // Prevent duplicate actions

//   setSaving(true); // Set saving to true at the start of the function

//   const nodeId = Date.now().toString(); // Unique ID for the node
//   console.log("Current Form Data:", formData);

//   // Create action data from formData
//   const actionData = {
//     action_name: formData.action_name || 'Email-Action', // Default or from form
//     type: 'send_email',
//     action_data: {
//       from_name: formData.from_name || 'Support',
//       from_email: formData.from_email || 'sukasini2001@gmail.com',
//       subject: formData.subject || 'Welcome to our Service!',
//       message: formData.content || 'Hello {{contact.name}}, Welcome to our service! Thank you for the Registration!',
//       attachments: formData.attachments || [], // Ensure attachments are passed
//     },
//   };

//   // Log action data to verify correctness
//   console.log("Action Data Created:", actionData);

//   // Define the position for the new node
//   const newNode = {
//     id: nodeId,
//     data: {
//       label: (
//         <div
//           style={{
//             display: 'flex',
//             alignItems: 'center',
//             backgroundColor: '#e7f3ff;', // Soft #e7f3ff background for the node box
//             borderRadius: '8px', // Rounded corners for modern look
//             padding: '12px 16px', // Adequate padding for spacing
//             // boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)', // Soft shadow for elevation
//             fontSize: '13px', // Slightly larger font size for readability
//             fontWeight: '500', // Medium bold text for better emphasis
//             color: '#333', // Dark text color for readability
//             // border: '1px solid #dcdcdc', // Light border to define the node boundaries
//             transition: 'all 0.3s ease', // Smooth transition for hover effects
//             cursor: 'pointer', // Pointer cursor for interactivity
//           }}
//         >
//           {/* Email Icon */}
//           <MailOutlined style={{ marginRight: 8, color: 'green', fontSize: '18px' }} />
          
//           {/* Action Name */}
//           <span style={{ flex: 1, fontSize: '16px', color: '#4a4a4a' }}>
//             {actionData.action_name}
//           </span>
//            {/* Dropdown for actions */}
//            <Dropdown
//         overlay={
//           <Menu>
//             <Menu.Item key="delete" onClick={() => handleDeleteNode(newNode.id)}>
//             <Button 
//              style={{
//              borderColor: 'red', 
//              borderStyle: 'solid', 
//              borderWidth: '1px',
//             color: 'red'  // Optional: Change the text color to red as well
            
//            }}
//           >
//             Delete
//           </Button>
//             </Menu.Item>
//             <Menu.Item key="edit">
//             <Button type='primary' onClick={() => handleNodeClick(formData, newNode.id)}>Edit</Button>
//             </Menu.Item>
//           </Menu>
//         }
//         trigger={['click']}
//       >
//         <UnorderedListOutlined
//           style={{
//             cursor: 'pointer',
//             fontSize: '18px',
//             color: '#4a4a4a',
//           }}
//         />
//       </Dropdown>
//         </div>
//       ),
//       type: 'action',
//       formData: actionData,
//     },
//     position: {
//       x: triggerNodePosition.x + offsetX, // Adds the horizontal offset
//       y: triggerNodePosition.y + offsetY, // Adds the vertical offset
//     },
//   };

//   // Add new node to the nodes array
//   setNodes((prevNodes) => [...prevNodes, newNode]);

//   // Send actionData to the API
//   sendActionDataToAPI(actionData)
//     .then(() => {
//       // Reset specific fields after saving by calling the reset function
//       resetForm();

//       // Close the drawer after saving
//       setEmailActionDrawerVisible(false);
//       console.log('Action Data:', actionData);

//       // Update first node tracking
//       setIsFirstNode(false);
//     })
//     .catch((error) => {
//       console.error('Error during save action:', error);
//     })
//     .finally(() => {
//       setSaving(false); // Reset saving state once action is complete
//     });
    
// };

// // Function to send action data to API using Axios
//   const sendActionDataToAPI = (actionData) => {
// console.log("action data", actionData);


//     const apiUrl = 'http://127.0.0.1:8000/api/actions';
  
//     return axios.post(apiUrl, actionData, {
//       headers: {
//         'Content-Type': 'application/json', // Set the content type to JSON
//       },
//     })
//     .then(response => {
//       console.log('Successfully sent action data:', response.data); // Log the success response
//       console.log('Successfully sent action data:', response.data.id); // Log the success response
//       setActionId(response.data.id);
//     })
//     .catch(error => {
//       console.error('Error sending action data:', error); // Handle errors
//       if (error.response) {
//         console.error('Error response:', error.response.data);
//       }
//       throw error; // Re-throw error to be caught in handleSaveAction
//     });
//   };

//   const closeDrawer = () => {
//     setEditAction({}); // Clear the editAction state
//     setNodeData(null); // Clear the nodeData
//     setIsDrawerOpen(false); // Close the drawer
//   };
  

//   const AddTriggerNode = ({ data }) => (
//     <div
//       style={{
//         padding: '10px',
//         // border: '1px solid #ccc',
//         borderRadius: '5px',
//         cursor: 'pointer',
//         backgroundColor: '#e6f7ff',
//       }}
//       onClick={data.onClick}
//     >
//   <div>
//   <Handle
//   type="target"
//   position="right"
//   style={{
//     background: '#e7f3ff',
//     left: '91%', // Position close to the right edge
//     width: '10px',
//     border: 'transparent',
//   }}
//   isConnectable={true} // Enable connections
// />


  
// {/* <Handle
//   type="source"
//   position="left"
//   style={{
//     background: '#e7f3ff',
//     left: '8%', // Position close to the left edge
//     width: '10px',
//     border: 'transparent',
//   }}
//   isConnectable={true} // Allow connections from this handle
// /> */}

// {/* Label for the node */}
// {data.label}

// <Handle
//   type="source"
//   position="bottom"
//   style={{
//     width: '30px',
//     height: '30px',
//     borderRadius: '50%',
//     background: 'blue',
//     color: 'white',
//     fontSize: '20px',
//     display: 'flex',
//     justifyContent: 'center',
//     alignItems: 'center',
//     cursor: 'pointer',
//     border: 'none',
//     boxShadow: '0 4px 8px rgba(0, 0, 0, 0.2)',
//   }}
//   isConnectable={true} // Enable connections
//   onClick={(event) => {
//     event.stopPropagation(); // Prevent the node click event
//     openActionDrawer(); // Open the Email action drawer when clicked
//   }}
// >
//   +
// </Handle>
// </div>
//   </div>
//   )

//   const nodeTypes = {
//     addTrigger: AddTriggerNode,
//    };

//    const handleEditNode = (selectedNode) => {
//     if (!selectedNode || !selectedNode.id) {
//       message.error("Invalid node selected for editing.");
//       return;
//     }
  
//     // Set the nodeData to the selected node
//     setNodeData(selectedNode); // Store the selected node in state
  
//     // Set the form fields to the selected node's data
//     setEditAction({
//       action_name: selectedNode.data.action_name || '',
//       from_name: selectedNode.data.from_name || '',
//       from_email: selectedNode.data.from_email || '',
//       subject: selectedNode.data.subject || '',
//       content: selectedNode.data.content || '',
//       testEmail: selectedNode.data.testEmail || '',
//       attachments: selectedNode.data.attachments || [],
//       template: selectedNode.data.template || 'Email', // Template default if not present
//     });
  
//     // Open the drawer to allow editing
//     setIsDrawerOpen(true); // Assuming you have a state to control drawer visibility
//   };
  
//   // Handle saving the edited trigger data
//   const handleSaveEditedTrigger = () => {
//     const updatedData = {
//       updated_at: new Date().toISOString(),
//       filters: triggerState.filters,
//       id: triggerId,
//       trigger_name: triggerState.workflowTriggerName, // Assuming this is what needs to be updated
//     };
  
//     axios
//       .put(`http://127.0.0.1:8000/api/triggers/${triggerId}`, updatedData)
//       .then((res) => {
//         console.log('Updated data from backend:', res.data);
//         message.success("Trigger Edited successfully.");
  
//         // Update the node dynamically in ReactFlow
//         setNodes((prevNodes) =>
//           prevNodes.map((node) =>
//             node.id === String(triggerId) // Match the node by ID
//               ? {
//                   ...node,
//                   data: {
//                     ...node.data,
//                     label: (
//                       <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
//                         <span style={{ flex: 1, fontSize: '13px', color: '#555' }}>
//                           {res.data.trigger_name}
//                         </span>
//                         {/* Dropdown menu with edit and delete */}
//                         <Dropdown overlay={menu(triggerId)} trigger={['click']} placement="bottomRight">
//                           <MenuOutlined
//                             style={{
//                               fontSize: '15px',
//                               color: '#4a4a4a',
//                               marginLeft: '8px',
//                               cursor: 'pointer',
//                             }}
//                             onMouseEnter={(e) => (e.target.style.color = '#1890ff')}
//                             onMouseLeave={(e) => (e.target.style.color = '#4a4a4a')}
//                           />
//                         </Dropdown>
//                       </div>
//                     ), // Update label with the new trigger name and menu
//                   },
//                 }
//               : node
//           )
//         );
  
//         // Now update the node data in the UI
//         handleAnotherNode(
//           {
//             id: res.data.id,
//             data: {
//               trigger_name: res.data.trigger_name, // Updated trigger name
//               filters: res.data.filters, // Updated filters
//             },
//           },
//           true // Passing `true` to update the existing node
//         );
  
//         setEditDrawerVisible(false); // Close the edit drawer
//       })
//       .catch((error) => {
//         console.error("Error updating trigger:", error.response || error);
//         message.error("Failed to update trigger.");
//       });
//   };
  

//  const handleAnotherNode = (nodeData, updateExistingNode = false) => {
//    setNodes((prevNodes) => {
//      console.log('Node data:', nodeData);

//     // Determine if the node is a trigger
//     const isTrigger = nodeData.type === 'Contact-Created' || nodeData.type === 'Appointment-Booked';

//     // Format filters to set customerId and customer name as "Sukasini"
//     const formattedFilters = nodeData.data.filters?.map((filter) => ({
//       ...filter,
//       customerId: filter.customer,  // Set the actual customer ID
//       customer: "Sukasini",  // Set name as "Sukasini"
//     })) || [];

//     // Update existing node if specified
//     if (updateExistingNode) {
//       return prevNodes.map((node) =>
//         node.id === nodeData.id
//           ? {
//               ...node,
//               data: {
//                 ...node.data,
//                 trigger_name: nodeData.data.trigger_name,  // Update trigger name
//                 filters: formattedFilters,  // Updated filters with "Sukasini" as name
//               },
//               label: (
//                 <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
//                   <span>{isTrigger ? nodeData.data.trigger_name : node.data.label}</span>
//                   <Dropdown overlay={menu(node.id)} trigger={['click']} placement="bottomRight">
//                     <MenuOutlined style={{ cursor: 'pointer' }} />
//                   </Dropdown>
//                 </div>
//               ),
//             }
//           : node
//       );
//     }

//     // Create a new node if not updating an existing one
//     const newNode = isTrigger
//       ? {
//           id: String(nodeData.id),
//           data: {
//             type: 'trigger',  // Mark as a trigger node
//             trigger_name: nodeData.data.trigger_name || 'Contact Created',  // Default trigger name
//             filters: formattedFilters.length > 0 ? formattedFilters : [  // Use formatted filters or defaults
//               {
//                 field: 'primary_phone',
//                 operator: 'equals',
//                 value: '1234567890',
//               },
//               {
//                 field: 'primary_email',
//                 operator: 'equals',
//                 value: 'user@example.com',
//               },
//             ],
//           },
//           position: { x: Math.random() * 250, y: Math.random() * 250 },  // Random position
//         }
//       : {
//           id: String(nodeData.id),
//           data: { label: nodeData.name },
//           position: { x: Math.random() * 250, y: Math.random() * 250 },
//         };

//     // Add label to the new node
//     newNode.data.label = (
//       <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
//         <span>{isTrigger ? newNode.data.trigger_name : newNode.data.label}</span>
//         <Dropdown overlay={menu(newNode.id)} trigger={['click']} placement="bottomRight">
//           <MenuOutlined style={{ cursor: 'pointer' }} />
//         </Dropdown>
//       </div>
//     );

//     // Add the new node or updated node to the state
//     return updateExistingNode ? prevNodes : [...prevNodes, newNode];
//   });
// };


// useEffect(() => {
//     // Set the content whenever the formData is updated (used in case of edits)
//     setContent(formData.content || '');
//     setTestEmail(formData.testEmail || '');
//   }, [formData]);

// const handleWorkflowTriggerChange = (value) => {
//   setTriggerState((prev) => ({
//     ...prev,
//     workflowTrigger: value,
//     workflowTriggerName: value.replace('-', ' '), // Formats the name nicely
//   }));
// };

// const toJSON = async (nodes, workflowName) => {
// console.log('name test',workflowName);

//   // Create a structured workflow object
//   const workflowData = {
//     workflow_name: fileName,
//     trigger_id: triggerId,    
//     actions_id: [actionId], // Array of action IDs
//   };
//   console.log('final',workflowData);
  

//   // Convert the workflow data to JSON
//   const jsonString = JSON.stringify(workflowData, null, 2);
//   const blob = new Blob([jsonString], { type: 'application/json' });

//   // Create a download link for the blob
//   const link = document.createElement('a');
//   link.href = URL.createObjectURL(blob);
//   link.download = `${fileName}.json`;
//   link.click();

//   // Send the data to the backend API
//   await sendWorkflowDataToAPI(workflowData);
// };

// const sendWorkflowDataToAPI = async (workflowData) => {
//   const apiUrl = 'http://127.0.0.1:8000/api/workflows';  // Backend API endpoint

//   try {
//     const response = await axios.post(apiUrl, workflowData, {
//       headers: {
//         'Content-Type': 'application/json',  // Telling the server you're sending JSON
//         'Accept': 'application/json',  // Requesting JSON response
//       },
//     });

//     console.log('Successfully sent workflow data:', response.data);
//   } catch (error) {
//     // Print the entire error response object for more information
//     console.error('Error sending workflow data to API:', error);

//     if (error.response) {
//       console.log('API Error Status:', error.response.status);  // Print the status code (500 or other)
//       console.log('API Error Data:', error.response.data);  // Print the backend error message
//     } else if (error.request) {
//       // The request was made, but no response was received
//       console.error('No response received from API:', error.request);
//     } else {
//       // Something happened in setting up the request
//       console.error('Error setting up the API request:', error.message);
//     }
//   }
// };

//  // Handle the edit button click
//  const handleTriggerEditNode = (nodeId) => {
//   console.log("Editing Node ID:", nodeId); // Debugging statement
//   setTriggerId(nodeId)
//   setEditingNodeId(nodeId);
//   setEditDrawerVisible(true);
// };

//  const closeEditDrawer = () => {
//     setEditDrawerVisible(false);
//   };

//   // useEffect(() => {
//   //   if (editingNodeId) {
//   //     const nodeData = findNodeDataById(editingNodeId); // Fetch node data by ID
//   //     if (nodeData) {
//   //       setTriggerState(nodeData); // Set triggerState with node's data for editing
//   //     }
//   //   }
//   // }, [editingNodeId]);

//  useEffect(() => {
//   if (editingNodeId) {
//     axios.get(`http://127.0.0.1:8000/api/triggers/${editingNodeId}`)
//       .then((response) => {
//         console.log("API Response Data:", response.data); // Inspect this

//         setTriggerState({
//           workflowTrigger: response.data.trigger_name|| "",
//           workflowTriggerName: response.data.trigger_name || "",
//           filters: response.data.filters,
//         });

//         // Once data is loaded, set flag to show the drawer
//         setIsDataLoaded(true);
//       })
//       .catch((error) => {
//         console.error("Error fetching node data:", error);
//       });
//   }
// }, [editingNodeId]);

//   // const handleFilterChange = (filterId, field, value) => {
//   //   const updatedFilters = triggerState.filters.map((filter) =>
//   //     filter.id === filterId ? { ...filter, [field]: value } : filter
//   //   );
//   //   setTriggerState({ ...triggerState, filters: updatedFilters });
//   // };

//   // console.log("URL",`Requesting: http://127.0.0.1:8000/api/triggers/${editingNodeId}`);

//   const addFilter = () => {
//   setTriggerState((prev) => ({
//     ...prev,
//     filters: [
//       ...prev.filters,
//       { id: Date.now(), customerId: '', operator: '' },
//      ],
//    }));
//  };

//   const deleteFilter = (filterId) => {
//     setTriggerState((prev) => ({
//       ...prev,
//       filters: prev.filters.filter((filter) => filter.id !== filterId),
//     }));
//   };

//    const handleSaveTrigger = () => {
//     const { workflowTrigger, workflowTriggerName } = triggerState;
  
//     console.log('Trigger State:', triggerState);
//     console.log('Selected Customer:', selectedCustomer);
//     console.log('Selected Operator:', selectedOperator);
//     console.log('test state', triggerState.workflowTriggerName);
  
//     if (workflowTrigger && workflowTriggerName && selectedCustomer && selectedOperator) {
//       const triggerData = {
//         trigger_name: workflowTriggerName,
//         filters: [
//           {
//             field: typeof selectedCustomer === 'string' ? selectedCustomer.trim() : selectedCustomer,
//             operator: selectedOperator,
//             value: "",
//           },
//         ].filter((filter) => filter.field && filter.operator),
//       };
  
//       console.log('Trigger Data:', triggerData);
  
//       // Send triggerData to the API
//       sendTriggerDataToAPI(triggerData)
//         .then((response) => {
//           const apiGeneratedId = response.data.id;  // Get the ID from the API response
//           console.log("pushed data",response.data);
//           setTriggerId(apiGeneratedId)
          
//           console.log('API Generated ID:', apiGeneratedId);
  
//           // Now create the node with the API-generated ID
//           const newNode = {
//             id: String(apiGeneratedId),  // Use the API generated ID as the node's ID
//             type: workflowTrigger,
//             name: (
//               <div
//       style={{
//         display: 'flex',
//         justifyContent: 'space-between',
//         alignItems: 'center',
//         backgroundColor: '#ffffff',
//         borderRadius: '12px',
//         padding: '12px 16px',
//         boxShadow: '0 6px 12px rgba(0, 0, 0, 0.1)',
//         fontSize: '16px',
//         fontWeight: '500',
//         color: '#333',
//         border: '1px solid #e0e0e0',
//         cursor: 'pointer',
//         transition: 'all 0.3s ease',
//       }}
//     >
      
//         <Dropdown overlay={menu(apiGeneratedId)} trigger={['click']} placement="bottomRight">
//                   <MenuOutlined
//                     style={{
//                       fontSize: '20px',
//                       color: '#4a4a4a',
//                       transition: 'color 0.2s ease',
//                       cursor: 'pointer',
//                     }}
//                     onMouseEnter={(e) => (e.target.style.color = '#1890ff')}
//                     onMouseLeave={(e) => (e.target.style.color = '#4a4a4a')}
//                   />
//                 </Dropdown>
//               </div>
//             ),
//             data: triggerData,
//           };
  
//           // Add the new node to the existing nodes
//           handleAnotherNode(newNode);
  
//           console.log("Saved Trigger Data with API ID:", triggerData);
//           console.log('Node Position:', { x: 200, y: 100 }); // Check node's fixed position
  
//           // Close the drawer after saving
//           setDrawerVisible(false);
  
//           // Update first node tracking
//           setIsFirstNode(false);
//         })
//         .catch((error) => {
//           console.error('Error sending trigger data:', error);  // Handle errors
//           if (error.response) {
//             console.error('Error response:', error.response.data);
//           }
//         });
//     } else {
//       console.log("Invalid trigger: Missing one or more fields");
//     }
//   };
  
//   // Function to send trigger data to API using Axios
//   const sendTriggerDataToAPI = (triggerData) => {
//     const apiUrl = 'http://127.0.0.1:8000/api/triggers';
  
//     return axios.post(apiUrl, triggerData, {
//       headers: {
//         'Content-Type': 'application/json', // Set the content type to JSON
//       },
//     })
//     .then(response => {
//       console.log('Successfully sent trigger data:', response.data);  // Log the success response
//       return response;  // Return the response so it can be used in the next `.then` block
//     })
//     .catch(error => {
//       console.error('Error sending trigger data:', error);  // Handle errors
//       throw error;  // Rethrow the error to be caught in the `handleSaveTrigger` function
//     });
//   };
  
//   const updatedNodes = nodes.map((node) =>
//     node.id === '0' ? { ...node, data: { ...node.data, onClick: handlePlusClick } } : node
//   );

//   const openEmailActionDrawer = () => {
//     closeActionDrawer(); // Close the action drawer before opening the email drawer
//     setEmailActionDrawerVisible(true);
//   };

//   const closeEmailActionDrawer = () => {
//     setEmailActionDrawerVisible(false);
//   };

//  const updateFilter = (index, key, value) => {
//     const updatedFilters = [...triggerState.filters];
//     updatedFilters[index][key] = value;
//     setTriggerState((prev) => ({ ...prev, filters: updatedFilters }));
//   };
  
//  const handleInputChange = (field, value) => {
//     setFormData((prevData) => ({
//       ...prevData,
//       [field]: value,  // Dynamically update the specific field
//     }));
//   };

// // Assumes nodes is available in the scope (for example, as a state)

// const handleNodeSelection = (node) => {
//   // Check if the selected node has the necessary data
//   if (node && node.data) {
//     // Update nodeData with the selected node's data
//     setNodeData({
//       id: node.id,
//       data: { ...node.data },
//     });

//     // Optionally, pre-fill the edit form with the selected node data
//     setEditAction({
//       action_name: node.data.action_name,
//       from_name: node.data.from_name,
//       from_email: node.data.from_email,
//       subject: node.data.subject,
//       content: node.data.content,
//       testEmail: node.data.testEmail,
//       attachments: node.data.attachments,
//       template: node.data.template,
//     });
//   } else {
//     console.log("Node selection error: Invalid node data", node);
//   }
// };

// const handleSubmit = async () => {
//   if (!nodeData) {
//     message.error('No node selected for editing. Please select a node.');
//     return;
//   }

//   // Construct the updated node data
//   const updatedNodeData = {
//     id: actionId, // Node ID
//     action_name: editAction.action_name, // Action name
//     type: 'send_email', // Example type, replace if dynamic
//     action_data: {
//       from_name: editAction.from_name,
//       from_email: editAction.from_email,
//       subject: editAction.subject,
//       message: editAction.content,
//       template: editAction.template,
//       testEmail: editAction.testEmail,
//       attachments: editAction.attachments || [],
//     },
//   };

//   console.log('Updated Node Data:', updatedNodeData);

//   try {
//     // Send the updated node data to the API using axios PUT request
//     const response = await axios.put(
//       `http://127.0.0.1:8000/api/actions/${actionId}`,
//       updatedNodeData,
//       {
//         headers: {
//           'Content-Type': 'application/json',
//         },
//       }
//     );

//     if (response.status === 200) {
//       const savedData = response.data; // API response with updated data

//       console.log('Updated data from backend:', savedData);

//       // Update the ReactFlow node state
//       setNodes((prevNodes) =>
//         prevNodes.map((node) =>
//           node.id === savedData.id
//             ? {
//                 ...node,
//                 data: {
//                   ...node.data,
//                   label: createNodeLabel(savedData, savedData.id), // Update the label dynamically
//                 },
//               }
//             : node
//         )
//       );

//       message.success('Action Edited successfully.');
//       closeDrawer(); // Close the drawer after saving
//     } else {
//       throw new Error('Failed to update action.');
//     }
//   } catch (error) {
//     console.error('Error updating data:', error);
//     message.error('Failed to update action.');
//   }
// };
//   const handleFileChange = (e) => {
//     const file = e.target.files[0];
//     if (file) {
//       setFilePath(file.name);
//     }
//   };  

//   const handleContentChange = (value) => {
//     setContent(value); // Update the content state
//   };

//   // Trigger file input click
//   const handleButtonClick = () => {
//     document.getElementById('fileInput').click();
//   };

//   const handleFileNameChange = (e) => {
//     setFileName(e.target.value); // Update state with input value
//   };

//   const closeActionDrawer = () => {
//     setActionDrawerVisible(false);
//   };

//       const exportFlow = () => {
//        const flowData = {  
//           nodes,
//           edges,
//       };
//     console.log(JSON.stringify(flowData, null, 2)); // Export as JSON format
//   };

//   const handleBackToWorkflow = () => {
//     navigate('/workflow'); // Replace '/workflow' with the actual route of your Workflow component
//   };
  
//   const openActionDrawer = () => {
//     setActionDrawerVisible(true);
 
//     formData.from_email = '';
//     formData.from_name = '';
//     formData.subject = '';
//   };

//   const updateNodeData = (id, newData) => {
//     setNodes((prevNodes) =>
//       prevNodes.map((node) =>
//         node.id === id ? { ...node, data: newData, label: newData.workflowTriggerName } : node
//       )
//     );
//   };
//   return (
//     <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh', padding: '16px' }}>
//     <div
//    style={{
//     marginBottom: '16px',
//     display: 'flex',
//     alignItems: 'center', // Ensures vertical alignment
//     justifyContent: 'space-between', // Spaces the button and input
//     width: '100%',
//     padding: '0 16px', // Optional padding for better spacing
//    }}
//   >
//    {/* Input field */}
//    <div
//   style={{
//     display: 'flex',
//     justifyContent: 'center', // Centers content horizontally
//     alignItems: 'center', // Centers content vertically
//     width: '100%',
//     marginBottom: '16px', // Optional spacing below the container
//   }}
// >
//   <Input
//     style={{
//       width: '100%',
//       maxWidth: '500px', // Restricts the input's width
//       height: '48px',
//     }}
//     onChange={handleFileNameChange}
//     placeholder="Enter file name"
//     value={fileName}
//   />
// </div>

//   </div>

//   <div style={{ flex: 1 }}>
//       <Row justify="space-between" align="middle" style={{ width: '100%' }}>
//         <Col>
//           <Dropdown overlay={<Menu />}>
//             <Button type='primary'>
//               <Space>
//                 Actions <DownOutlined />
//               </Space>
//             </Button>
//           </Dropdown>
//         </Col>

//         <Col flex="auto" style={{ display: 'flex', justifyContent: 'center' }}>
//           <Tabs defaultActiveKey="1">
//             <Tabs.TabPane tab="Actions" key="1" />
//             <Tabs.TabPane tab="Settings" key="2" />
//             <Tabs.TabPane tab="History" key="3" />
//             <Tabs.TabPane tab="Status" key="4" />
//           </Tabs>
//         </Col>

//         <Col>
//           <Button style={{ marginRight: '16px' }} onClick={exportFlow} type='primary'>Export Flow</Button>
//           <Button
//             className={
//               "rounded-lg py-2.5 px-10 text-sm font-medium rounded-l-lg leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 bg-blue-800 shadow text-white"
//              }
//             onClick={() => toJSON(nodes,triggerState, fileName || 'default')} 
//               type='primary'
//                 >
//            Update
//                 </Button>
//           <Switch style={{ marginLeft: '8px' }} /> Publish
//           <Divider type="vertical" style={{ marginLeft: '16px' }} />
//         </Col>
//       </Row>
//     </div>

//     <div className="wrapper" ref={reactFlowWrapper}>
//       <ReactFlow
//         nodes={updatedNodes}
//         edges={edges}
//         onNodesChange={onNodesChange}
//         onEdgesChange={onEdgesChange}
//         onConnect={(params) => setEdges((eds) => addEdge(params, eds))}
//         onClick={onCanvasClick} // Capture click events
//         onNodeDragStop={onNodeDragStop}
//         onNodeDragStart={(event, node) => event.preventDefault()} // Prevent dragging
//         fitView
//         fitViewOptions={{ padding: 2 }}
//         nodeTypes={nodeTypes}
//         zoomOnScroll={false} // Disable zooming to ensure position is not altered
//         panOnScroll={false} // Disable panning on scroll if needed
// />
//       {/* Button aligned to the left */}
//       <Button
//   type="primary"
//   icon={<ArrowLeftOutlined />}
//   onClick={handleBackToWorkflow}
//   style={{
//     position: 'absolute', // Positions the button relative to the page
//     top: '16px', // Distance from the top of the page
//     left: '16px', // Distance from the left of the page
//     display: 'flex',
//     alignItems: 'center',
//     padding: '0 12px', // Adjust button padding for a consistent look
//     zIndex: 1000, // Ensures it stays above other elements if overlapping occurs
//   }}
// >
//   Back To WorkFlow
// </Button>
//       <div className="zoom-controls" style={{ position: 'absolute', top: 10, right: 10 }}>
//             <Button onClick={zoomIn} style={{ margin: '5px' }}>+</Button>
//              <Button onClick={zoomOut} style={{ margin: '5px' }}>-</Button>
//      </div>
    
//    <Drawer
//   title="Edit Trigger"
//   placement="right"
//   closable={true}
//   visible={editDrawerVisible}
//   onClose={() => setEditDrawerVisible(false)}
// >
//   {/* Workflow Trigger Field */}
//   <label>Choose a Workflow Trigger:</label>
//   <Select
//     value={triggerState.workflowTrigger} // Prefill with the workflowTrigger value
//     onChange={(value) =>
//       setTriggerState((prevState) => ({
//         ...prevState,
//         workflowTrigger: value, // Update workflowTrigger
//         workflowTriggerName: value, // Synchronize workflowTriggerName
//       }))
//     }
//     style={{ width: '100%' }}
//   >
//     <Option value="Contact-Created">Contact Created</Option>
//     <Option value="Appointment-Booked">Appointment Booked</Option>
//     </Select>

//   <br /><br />
//   {/* Workflow Trigger Name Field */}
//   <label>Workflow Trigger Name:</label>
//   <Input
//     value={triggerState.workflowTriggerName} // Prefill with the workflowTriggerName value
//     onChange={(e) =>
//       setTriggerState((prevState) => ({
//         ...prevState,
//         workflowTriggerName: e.target.value, // Allow manual edit of workflowTriggerName
//       }))
//     }
//     style={{ width: '100%' }}
//   />
//   <br /><br />

//   {/* Filters Section */}
//   <label>Filters:</label>
//   {triggerState.filters?.map((filter, index) => (
//     <div key={filter.id} style={{ display: 'flex', alignItems: 'center', marginBottom: '8px' }}>
//       {/* Operator Select */}
//       <Select
//         value={filter.operator || ''} // Prefill with the selected operator
//         style={{ width: '50%', marginRight: '8px' }}
//         placeholder="Select an operator"
//         onChange={(value) => {
//           const updatedFilters = [...triggerState.filters];
//           updatedFilters[index] = { ...updatedFilters[index], operator: value };
//           setTriggerState((prev) => ({ ...prev, filters: updatedFilters }));
//         }}
//       >
//         <Option value="Selected">Selected</Option>
//         <Option value="Ignored">Ignored</Option>
//         <Option value="All">All</Option>
//       </Select>

//       {/* Customer Select */}
//       <Select
//         value={filter.customer || ''} // Prefill with the selected customer
//         showSearch
//         style={{ width: '50%' }}
//         placeholder="Select a customer"
//         optionFilterProp="children"
//         onChange={(value) => {
//           const updatedFilters = [...triggerState.filters];
//           updatedFilters[index] = { ...updatedFilters[index], customer: value };
//           setTriggerState((prev) => ({ ...prev, filters: updatedFilters }));
//         }}
//         filterOption={(input, option) =>
//           option.children.toLowerCase().includes(input.toLowerCase())
//         }
//       >
//         {customer.map((cust) => (
//           <Option key={cust.id} value={cust.id}>
//             {cust.name}
//           </Option>
//         ))}
//       </Select>

//       <DeleteOutlined
//         style={{ marginLeft: '8px', color: 'red', cursor: 'pointer' }}
//         onClick={() => deleteFilter(filter.id)}
//       />
//     </div>
//   ))}

//   <Button icon={<PlusOutlined />} onClick={addFilter} style={{ width: '100%' }}>
//     Add Filter
//   </Button>

//   <br /><br />
//   <Button type="primary" onClick={handleSaveEditedTrigger}>
//     Save Changes
//   </Button>
// </Drawer>

//       <Drawer
//         title="Create Trigger"
//         placement="right"
//         closable={true}
//         onClose={triggerClose}
//         open={drawerVisible} // Use 'open' instead of 'visible'
//       >
//         <label>Choose a Workflow Trigger:</label> 
//         <Select
//           value={triggerState.workflowTrigger}
//           onChange={handleWorkflowTriggerChange}
//           style={{ width: '100%' }}
//         >
//           <Option value="Contact-Created">Contact Created</Option>
//           <Option value="Appointment-Booked">Appointment Booked</Option>
//         </Select>

//         <br /><br />
//         <label>Workflow Trigger Name:</label>
//         <Input
//           value={triggerState.workflowTriggerName}
//           onChange={(e) =>
//             setTriggerState((prev) => ({
//               ...prev,
//               workflowTriggerName: e.target.value,
//             }))
//           }
//         />
//         <br /><br />
//         <label>Filters:</label>
//         {triggerState.filters.map((filter) => (
//           <div key={filter.id} style={{ display: 'flex', alignItems: 'center', marginBottom: '8px' }}>
//             <Select
//               style={{ width: '100%' }}
//               placeholder="Select an operator"
//               onChange={(value) => setSelectedOperator(value)}
//             >
//               <Option value="Selected">Selected</Option>
//               <Option value="Ignored">Ignored</Option>
//               <Option value="All">All</Option>
//             </Select>

//         <Select
//             showSearch
//             style={{ width: '100%' }}
//             placeholder="Select a customer"
//             optionFilterProp="children"
//             onChange={(value, option) => setSelectedCustomer(option.children)}  // Correctly sets the selected customer
//             filterOption={(input, option) =>
//           option.children.toLowerCase().includes(input.toLowerCase())
//           }
//         >
//   {customer.map((customers) => (
//     <Option key={customers.id} value={customers.id}>
//       {customers.name}  {/* Display customer names */}
//     </Option>
//   ))}
// </Select>
//       <DeleteOutlined
//               style={{ marginLeft: '8px', color: 'red', cursor: 'pointer' }}
//               onClick={() => deleteFilter(filter.id)}
//             />
//           </div>
//         ))}
        
//         <Button icon={<PlusOutlined />} onClick={addFilter} style={{ width: '100%' }}>
//           Add Filter
//         </Button>

//         <br /><br />
//         <Button type="primary" onClick={handleSaveTrigger}>Save Trigger</Button>
//       </Drawer>
//       <div>
//       <Drawer
//   title="Edit Email Action"
//   placement="right"
//   visible={isDrawerOpen}
//   onClose={closeDrawer}
//   width={400}
//   afterOpenChange={() => {
//     // Transfer data from formData to editAction when the drawer is opened
//     setEditAction({
//       action_name: formData.action_name || '',
//       from_name: formData.from_name || '',
//       from_email: formData.from_email || '',
//       subject: formData.subject || '',
//       content: formData.content || '',
//       testEmail: formData.testEmail || '',
//       attachments: formData.attachments || '',
//     });
//   }}
// >
//   <Form layout="vertical">
//     {/* Action Name */}
//     <Form.Item
//       label="Action Name"
//       rules={[{ required: true, message: "Please input the action name!" }]}
//     >
//       <Input
//         value={editAction.action_name}
//         onChange={(e) =>
//           setEditAction({ ...editAction, action_name: e.target.value })
//         }
//       />
//     </Form.Item>

//     {/* From Name */}
//     <Form.Item
//       label="From Name"
//       rules={[{ required: true, message: "Please input your name!" }]}
//     >
//       <Input
//         value={editAction.from_name}
//         onChange={(e) =>
//           setEditAction({ ...editAction, from_name: e.target.value })
//         }
//       />
//     </Form.Item>

//     {/* From Email */}
//     <Form.Item
//       label="From Email"
//       rules={[
//         { required: true, type: "email", message: "Please input a valid email!" },
//       ]}
//     >
//         onChange={(e) =>
//       <Input
//         value={editAction.from_email}
//           setEditAction({ ...editAction, from_email: e.target.value })
//         }
//       />
//     </Form.Item>

//     {/* Subject */}
//     <Form.Item
//       label="Subject"
//       rules={[{ required: true, message: "Please input the subject!" }]}
//     >
//       <Input
//         value={editAction.subject}
//         onChange={(e) =>
//           setEditAction({ ...editAction, subject: e.target.value })
//         }
//       />
//     </Form.Item>
//     <Form.Item
//   label="Templates"
//   name="template"
//   rules={[{ required: true, message: 'Please select a template!' }]}
// >
//   <Select
//     value={editAction.template} // Prefill from editAction state
//     onChange={(value) =>
//       setEditAction((prev) => ({ ...prev, template: value }))
//     }
//     style={{ width: '100%' }}
//   >
//     <Select.Option value="email">Email</Select.Option>
//     <Select.Option value="sms">SMS</Select.Option>
//     <Select.Option value="call">Call</Select.Option>
//     <Select.Option value="whatsapp">WhatsApp</Select.Option>
//   </Select>
// </Form.Item>

//     {/* ReactQuill for Content */}
//     <Form.Item label="Message">
//       <ReactQuill
//         value={editAction.content}
//           setEditAction({ ...editAction, content: value })
//         onChange={(value) =>
//         }
//         theme="snow"
//       />
//     </Form.Item>

//     {/* File Attachment */}
//     <input
//       type="file"
//       id="fileInput"
//       style={{ display: "none" }}
//       onChange={(e) => {
//         const file = e.target.files[0];
//         if (file) {
//           setEditAction({ ...editAction, attachments: file.name });
//         }
//       }}
//     />
//     <Button
//       icon={<UploadOutlined />}
//       onClick={() => document.getElementById("fileInput").click()}
//     >
//       Add Attachment
//     </Button>
//     {editAction.attachments && <p>Selected File: {editAction.attachments}</p>}

//     {/* Test Email */}
//     <Form.Item label="Test Mail">
//       <Input
//         placeholder="Enter test email"
//         value={editAction.testEmail}
//         onChange={(e) =>
//           setEditAction({ ...editAction, testEmail: e.target.value })
//         }
//         style={{ width: "200px", marginRight: 8 }}
//       />
//       <Button type="primary">Test Mail</Button>
//     </Form.Item>

//     {/* Save Changes Button */}
//     <Form.Item>
//       <Button type="primary" onClick={handleSubmit}>
//         Save Changes
//       </Button>
//     </Form.Item>
//   </Form>
// </Drawer>



//   {/* Choose an Action Drawer */}
//       <Drawer
//         title="Choose an Action"
//         placement="right"
//         onClose={closeActionDrawer}
//         visible={actionDrawerVisible}
//       >
//         <div>
//           <Button onClick={openEmailActionDrawer} type="primary">
//             <MailOutlined /> Email
//           </Button>
//         </div>
//         <p>
//           <Button>
//             <PhoneOutlined /> Call
//           </Button>
//         </p>
//         <p>
//           <Button>
//             <MessageOutlined /> SMS
//           </Button>
//         </p>
//         <p>
//           <Button>
//             <WhatsAppOutlined /> WhatsApp
//           </Button>
//         </p>
//       </Drawer>
//    {/* Email Action Drawer */}
//   <Drawer
//     title="Email Action"
//     placement="right"
//     visible={emailActionDrawerVisible}
//     onClose={closeEmailActionDrawer} // Ensure this function properly closes the drawer
// >
//   <Form  form={form} layout="vertical" onFinish={handleSaveAction}>
//     <Form.Item
//       label="Action Name"
//       name="action_name"
//       rules={[{ required: true, message: 'Please input the action name!' }]}
//     >
//       <Input onChange={(e) => handleInputChange('action_name', e.target.value)} />
//     </Form.Item>

//     <Form.Item
//       label="From Name"
//       name="from_name"
//       rules={[{ required: true, message: 'Please input your name!' }]}
//     >
//       <Input
//         value={formData.from_name}
//         onChange={(e) => handleInputChange('from_name', e.target.value)}      />
//     </Form.Item>

//     <Form.Item
//       label="From Email"
//       name="from_email"
//       rules={[{ required: true, type: 'email', message: 'Please input a valid email!' }]}
//     >
//       <Input
//         value={formData.from_email}
//         onChange={(e) => handleInputChange('from_email', e.target.value)}
//       />
//     </Form.Item>

//     <Form.Item
//       label="Subject"
//       name="subject"
//       rules={[{ required: true, message: 'Please input the subject!' }]}
//     >
//       <Input
//         value={formData.subject}
//         onChange={(e) => handleInputChange('subject', e.target.value)}
//       />
//     </Form.Item>

//     <Form.Item label="Templates" name="template" rules={[{ required: true, message: 'Please select a template!' }]}>
//       <Select
//         value={formData.template}
//         onChange={(value) => handleInputChange('template', value)}
//         style={{ width: '100%' }}
//       >
//         <Select.Option value="email">Email</Select.Option>
//         <Select.Option value="sms">SMS</Select.Option>
//         <Select.Option value="call">Call</Select.Option>
//         <Select.Option value="whatsapp">WhatsApp</Select.Option>
//       </Select>
//     </Form.Item> 

//     <Form.Item label="Message">
//       <ReactQuill
//         value={formData.content}
//         onChange={(value) => handleInputChange('content', value)}
//       />
//     </Form.Item>

//     <input
//       type="file"
//       id="fileInput"
//       style={{ display: 'none' }}
//       onChange={handleFileChange}
//     />

//     <Button icon={<UploadOutlined />} onClick={handleButtonClick}>
//       Add Attachment
//     </Button>
//     {filePath && <p>Selected File: {filePath}</p>}

//     <Form.Item label="Test Mail">
//       <Input
//         placeholder="Enter test email"
//         value={formData.testEmail}
//         onChange={(e) => handleInputChange('testEmail', e.target.value)}
//         style={{ width: '200px', marginRight: 8 }}
//       />
//       <Button type="primary">Test Mail</Button>
//     </Form.Item>

//     <Form.Item>
//       <Button type="primary" htmlType="submit">
//         Save Action
//       </Button>
//     </Form.Item>
//   </Form>
// </Drawer>

//     </div>
//     </div>
//   </div>
// );
// };

// export default () => (
// <ReactFlowProvider>
//   <AddNodeOnEdgeDrop />
// </ReactFlowProvider>
// );

