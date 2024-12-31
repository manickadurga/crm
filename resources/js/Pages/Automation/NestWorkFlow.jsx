import React, {useCallback,useState,useEffect} from "react";
import {
  getBezierPath,
  ReactFlow,
  useNodesState,
  useEdgesState,
  addEdge,
} from "@xyflow/react";
import { useParams,useNavigate } from "react-router-dom";
import { PlusOutlined,ArrowLeftOutlined,CalendarOutlined,UserSwitchOutlined,UserAddOutlined,SearchOutlined,
  StopOutlined,TagOutlined,ClockCircleOutlined,DeleteOutlined,PlusCircleOutlined,BellOutlined, UserDeleteOutlined,EditOutlined,FileTextOutlined} from "@ant-design/icons";
import { Button,Row,Col,Drawer,Input,Select } from "antd";
import { SmoothStepEdge } from '@xyflow/react'; // Import SmoothStepEdge
import "@xyflow/react/dist/style.css";
import { getDataFunction } from '../../API';
DeleteOutlined

export default function NestWorkFlow({  sourceX, sourceY, targetX, targetY, sourcePosition, targetPosition }){
  const [] = getBezierPath({
    sourceX,
    sourceY,
    sourcePosition,
    targetX,
    targetY,
    targetPosition,
  });

 const [triggerNodePosition, setTriggerNodePosition] = React.useState({ x: 250, y: 100 });
 const [triggerCount, setTriggerCount] = useState(0); // Counter for trigger nodes
 const [drawerVisible, setDrawerVisible] = useState(false); // State for the drawer
 const [isDrawerVisible, setIsDrawerVisible] = useState(false);
 const [workFlow,setWorkFlow] = useState([])
 const [triggerName, setTriggerName] = useState('');
 const [detailDrawer, setDetailDrawer] = useState(false);
 const [workflowTriggerName, setWorkflowTriggerName] = useState('');
 const [selectedAction, setSelectedAction] = useState("");
 const [filters, setFilters] = useState([]); // State to store filter input fields
 const [drawerContentType, setDrawerContentType] = useState(""); // Determines the content of the drawer
 const navigate = useNavigate(); // React Router's navigate hook
 const [actionDrawer, setActionDrawer] = useState(false);

 const handleAddFilter = () => {
  setFilters([...filters, { operator: '', customer: '' }]);
};

  const actions = [
    { name: "Create Contact", icon: <UserAddOutlined /> },
    { name: "Find Contact", icon: <SearchOutlined /> },
    { name: "Update Contact Field", icon: <UserSwitchOutlined /> },
    { name: "Add Contact Tag", icon: <TagOutlined /> },
    { name: "Remove Contact Tag", icon: <UserDeleteOutlined /> },
    { name: "Assign User", icon: <UserAddOutlined /> },
    { name: "Remove Assigned User", icon: <UserDeleteOutlined /> },
    { name: "Edit Conversation", icon: <EditOutlined /> },
    { name: "Enable/Disable DND", icon: <BellOutlined /> },
    { name: "Add to Notes", icon: <EditOutlined /> },
  ];

const handleFilterChange = (index, key, value) => {
  const updatedFilters = [...filters];
  updatedFilters[index][key] = value;
  setFilters(updatedFilters);
};

 const renderAddNewTriggerContent = () => (
  <div>
    {/* Title and Description */}
    <h4 style={{ fontSize: "16px", fontWeight: "bold", marginBottom: "20px" }}>
      Adds a workflow trigger, and on execution, the Contact gets added to the workflow
    </h4>
    
    {/* Search Input */}
    <Input.Search placeholder="Search triggers..." style={{ marginBottom: 20 }} />
    
    {/* All Triggers Button */}
    <Button style={{ marginBottom: 20, width: "100%" }}>
      All Triggers
    </Button>

{/* Contact Section */}
    <h4>Contact</h4>
    {[
      { name: "Birthday Reminder", icon: <CalendarOutlined /> },
      { name: "Contact Changed", icon: <UserSwitchOutlined /> },
      { name: "Contact Created", icon: <UserAddOutlined /> },
      { name: "Contact DND", icon: <StopOutlined /> },
      { name: "Contact Tag", icon: <TagOutlined /> },
      { name: "Custom Date Reminder", icon: <ClockCircleOutlined /> },
      { name: "Notes Added", icon: <FileTextOutlined /> }, // Represents notes being added
      { name: "Note Changed", icon: <EditOutlined /> },   // Represents editing or changing a note
      { name: "Task Added", icon: <PlusCircleOutlined /> }, // Represents adding a  task
      { name: "Task Reminder", icon: <BellOutlined /> },
    ].map((button, index) => (
   <Button
          key={index}
          type="default"
          style={{
            marginBottom: "10px",
            width: "100%",
            textAlign: "left",
            padding: "10px 15px",
            display: "flex",
            alignItems: "center",
            border: "1px solid #d9d9d9",
            borderRadius: "6px",
            fontWeight: "500",
            backgroundColor: "#fff",
            color: "#333",
          }}
          onClick={() => handleButtonClick(button.name)}
        >
          {/* Icon Styling */}
          <span
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              width: "30px",
              color: "blue",
              fontSize: "18px",
            }}
          >
            {button.icon}
          </span>
          
          {/* Button Text */}
          <span style={{ marginLeft: "10px", flex: 1 }}>{button.name}</span>
        </Button>

    ))}
  </div>
);
// Default Content for Other Nodes
const renderDefaultContent = () => <p>This is an empty drawer.</p>;

const handleCloseDrawer = () => {
  setIsDrawerVisible(false);  // Close the drawer
};

const handleDetailDrawerClose = () => setDetailDrawer(false);

const handleAction = () => {
  setActionDrawer(true)
}

const handleActionDetail = (actionName) => {
  setSelectedAction(actionName);
  setDetailDrawer(true);
};

const handleDeleteFilter = (index) => {
  const updatedFilters = filters.filter((_, i) => i !== index);
  setFilters(updatedFilters);
};

const handleAddNewNode = () => {
  setTriggerCount((prevCount) => {
    const newTriggerId = `Trigger ${prevCount + 1}`;
    const isOdd = (prevCount + 1) % 2 !== 0;
    const baseOffsetX = 230;
    const newNodePosition = {
      x: triggerNodePosition.x + (isOdd ? -baseOffsetX : baseOffsetX) * Math.ceil((prevCount + 1) / 2),
      y: triggerNodePosition.y,
    };

    const newNode = {
      id: newTriggerId,
      type: "input",
      position: newNodePosition,
      data: { label: newTriggerId, type: "trigger" },
      style: {
        fontWeight: "bold",
        borderRadius: "6px",
        padding: "20px 40px",
        border: "2px dotted black",
        width: "220px",
        height: "60px",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        textAlign: "center",
       },
    };

    setNodes((prevNodes) => {
      const updatedNodes = [...prevNodes, newNode];

      if (firstActionNodeId) {
        const newEdge = {
          id: `e${newTriggerId}-${firstActionNodeId}`,
          source: newTriggerId,
          target: firstActionNodeId,
       };
        setEdges((prevEdges) => [...prevEdges, newEdge]);
      }
      return updatedNodes;
    });
    return prevCount + 1;
  });
};

 const loadedNodes = [
 {
    id: '1',
    type: 'input',
    position: { x: 249, y: 59 },
    data: { label:"Add New Trigger",},
    style: {
      alignItems: "center",
      backgroundColor: "#e6ffff",
      border: "2px dotted blue",
      borderRadius: "6px",
      color: "blue",
      display: "flex",
      fontWeight: "bold",
      height: "60px",
      justifyContent: "center",
      padding: "20px 40px",
      textAlign: "center",
      width: "220px",
    },
    measured: { width: 220, height: 60 },
  }, 
{id: '2', position: {x: 330, y: 410}, data: {label: 'END'}, type: 'output', 
  style: {
    alignItems: "center",
    backgroundColor: "silver",
    border: "1px solid #ccc",
    borderRadius:"10px",
    color: "black",
    display: "flex",
    fontWeight: "bold",
    height: "30px",
    justifyContent: "center",
    padding: "5px 10px",
    width: "60px"}, 
    measured
    : 
    {width: 60, height: 30} 
   },
 {id: 'Trigger 1', type: 'input', position: {x: 20, y: 100}, data: {label: 'Trigger 1', type: 'trigger'}, 
   style: {
    alignItems: "center",
    display: "flex",
    border: "2px dotted black",
    borderRadius: "6px",
    fontWeight: "bold",
    height: "60px",
    justifyContent: "center",
    padding: 
    "20px 40px",
    textAlign: "center",
    width: "220px"
  }, 
  
  measured
  : 
  {width: 220, height: 60} 
},

{id: 'Trigger 2', type: 'input', position: {x: 480, y: 100}, data: {label: 'Trigger 2', type: 'trigger'}, 
style: 
{
  alignItems: "center",
  border: "2px dotted black",
  borderRadius: "6px",
  display: "flex",
  fontWeight: "bold",
  height: "60px",
  justifyContent: "center",
  padding: "20px 40px",
  textAlign: "center",
  width: "220px"
  }, 
 measured: {width: 220, height: 60},
},

{id: 'Action 1', position: {x: 262, y: 217.5}, data: {label: 'Action 1', type: 'action'}, 
  style: {
  alignItems: "center",
  border: "2px dotted black",
  display: "flex",
  height: "50px",
  justifyContent: "center",
  width: "200px"
}, 
measured: {width: 200, height: 50}, }
];

const loadedEdges = [
  [
      {
          "id": "e1-Action 1",
          "source": "1",
          "target": "Action 1",
          "type": "smoothstep"
      },
      {
          "id": "eAction 1-2",
          "source": "Action 1",
          "target": "2",
          "type": "smoothstep"
      },
      {
          "id": "eTrigger 1-Action 1",
          "source": "Trigger 1",
          "target": "Action 1",
          "style": {
              "stroke": "#4A90E2",
              "strokeWidth": 2
          }
      },
      {
          "id": "eTrigger 2-Action 1",
          "source": "Trigger 2",
          "target": "Action 1",
          "style": {
              "stroke": "#4A90E2",
              "strokeWidth": 2
          }
      }
  ]
 ]

 const handleSaveAction = () => {
  const actionData = {
    actionName: selectedAction,
    filters,
  };
  console.log("Action Data:", actionData); // Pass this data to `handleAddNode`
  setDetailDrawer(false);
  setActionDrawer(false);
  // Add your logic to generate the action node with actionData
};

const flattenedEdges = loadedEdges.flat();
console.log(flattenedEdges);
const handleSave = () => {
  // Save logic goes here (e.g., send data to an API or store in a state)
  console.log('Saved filters:', filters);
  handleCloseDrawer();
};

 useEffect(() => {
  if (id) {
    // Set nodes and edges correctly
    setNodes(loadedNodes || []); // Use loadedNodes or fallback to an empty array
    setEdges(loadedEdges.flat() || []); // Flatten the edges array if it's nested
  }
}, []);

const { id } = useParams(); // Get the ID from the URL params

const initialNodes = [
  {
    id: "1",
    type: 'input',
    position: { x: triggerNodePosition.x, y: triggerNodePosition.y },
    data: {
      label: (
        <div style={{ display: "flex", alignItems: "center", justifyContent: "center", }} 
        onClick={(e) => {
          e.stopPropagation(); // Prevent parent click events
          // handleAddNewNode(); // Trigger node creation
          onNodeClick("addNewTrigger"); // Add this function call to trigger the correct drawer
        }}>
          <PlusOutlined style={{ color: "blue", fontSize: "16px",}} />
          <span style={{ color: "blue", fontWeight: "bold" }}>Add New Trigger</span>
   </div>
      ),
    },
    style: {
      backgroundColor: "#e6ffff",
      color: "blue",
      fontWeight: "bold", 
      borderRadius: "6px",
      padding: "20px 40px",
      border: "2px dotted blue",
      width: "220px",
      height: "60px",
      display: "flex",
      justifyContent: "center",
      alignItems: "center",
      textAlign: "center",
    },  
  },
    {
      id: "2",
      position: { x: 330, y: 330 },
      data: { label: "END" },
      type: "output",
      style: {
        backgroundColor: "silver",
        color: "black",
        fontWeight: "bold",
        borderRadius: "10px",
        padding: "5px 10px",
        border: "1px solid #ccc",
        width: "60px",
        height: "30px",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
      },
      connectable: true, // Make it connectable
    },
  ];

 const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);

 const initialEdges = [
    {
      id: "e1-2",
      source: "1",
      target: "2",  
      type: 'step',
      data: {
        onClickPlus: (addNodeFunction) => {
          const newNodeId = `${Math.random()}`; // Generate a unique ID
          addNodeFunction(newNodeId);
        },
      },
    },
  ];

  const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);
  const [firstActionNodeId, setFirstActionNodeId] = useState(null);

  console.log("Nodes",nodes)
  console.log("Edges",edges)
  
  const onConnect = useCallback(
    (params) => setEdges((eds) => addEdge(params, eds)),
    [setEdges]
  );

  useEffect(() => {
    if (firstActionNodeId) {
      setEdges((prevEdges) => {
        const triggerNodes = nodes.filter((node) => node.data?.type === "trigger");
        const newEdges = triggerNodes.map((triggerNode) => ({
          id: `e${triggerNode.id}-${firstActionNodeId}`,
          source: triggerNode.id,
          target: firstActionNodeId,
          style: {
            stroke: "#4A90E2",
            strokeWidth: 2,
          },
        }));
  
        // Remove old trigger-to-action edges and add the new ones
        return [
          ...prevEdges.filter((edge) => !triggerNodes.some((node) => edge.source === node.id)),
          ...newEdges,
        ];
      });
    }
  }, [firstActionNodeId, nodes]);

  useEffect(() => {
    const firstActionNode = findFirstActionNodeId(nodes);
    if (firstActionNode!== firstActionNodeId) {
      setFirstActionNodeId(firstActionNode);
    }
  }, [nodes, firstActionNodeId]);
  
  
  const findFirstActionNodeId = (nodes) => {
    const actionNodes = nodes.filter((node) => node.data?.type === "action");
    if (actionNodes.length === 0) return null;
  
    // Sort by `y` position and return the id of the node with the lowest `y`
    const firstActionNode = actionNodes.reduce((highestNode, currentNode) => {
      return currentNode.position.y < highestNode.position.y ? currentNode : highestNode;
    }, actionNodes[0]);
  
    return firstActionNode.id;
  };

    const handleBackToWorkflow = () => {
    navigate('/workflow'); // Replace '/workflow' with the actual route of your Workflow component
  };

  const handleActionDrawerClose = () => {
    setActionDrawer(false);
  };

  const handleAddNode = useCallback(
  (sourceNodeId, targetNodeId) => {
    // Find the highest existing Action node number
    const existingActionIds = nodes
      .map((node) => node.id)
      .filter((id) => id.startsWith("Action"))
      .map((id) => parseInt(id.replace("Action", ""), 10));

    const nextActionNumber = existingActionIds.length
      ? Math.max(...existingActionIds) + 1
      : 1; // Start from 1 if no Action nodes exist

    const newActionNodeId = `Action ${nextActionNumber}`;

    const sourceNode = nodes.find((node) => node.id === sourceNodeId);
    const targetNode = nodes.find((node) => node.id === targetNodeId);

    if (!sourceNode || !targetNode) return;

    const newNode = {
      id: newActionNodeId,
      position: {
        x: 262,
        y: (sourceNode.position.y + targetNode.position.y) / 2,
      },
      data: { label: newActionNodeId, type: "action" },
      style: {
        width: "200px",
        height: "50px",
        border: "2px dotted black",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
      },
    };

  const newEdges = [
      { id: `e${sourceNodeId}-${newActionNodeId}`,source: sourceNodeId,target: newActionNodeId,type: 'smoothstep',},
      { id: `e${newActionNodeId}-${targetNodeId}`,source: newActionNodeId, target: targetNodeId, type: 'smoothstep',},
    ];
    
   // Update nodes and edges
    setNodes((nds) =>
      nds
        .map((node) => {
          if (node.id === sourceNodeId) {
            return { ...node, position: { ...node.position, y: node.position.y  } };
          }
          if (node.id === targetNodeId) {
            return { ...node, position: { ...node.position, y: node.position.y + 80 } };
          }
          return node;
        })
        .concat(newNode)
    );
    setEdges((eds) =>
      eds
        .filter((edge) => !(edge.source === sourceNodeId && edge.target === targetNodeId)) // Remove old edge
        .concat(newEdges) // Add new edges
    );
  },
  [nodes, setNodes, setEdges]
);

const handleButtonClick = (name) => {
  // Close the existing drawer if it is open
  setIsDrawerVisible(false);

  // After closing the drawer, open a new one with a slight delay
  setTimeout(() => {
    setTriggerName(name);
    setWorkflowTriggerName(name);
    setIsDrawerVisible(true);  // Open the new drawer
  }, 300);  // Delay to ensure the previous drawer closes before opening a new one
};

const onNodeClick = useCallback((nodeType) => {
  console.log("NodeType:", nodeType); // Optional: Debugging
  setDrawerVisible(true); // Open the drawer when a node is clicked
  setDrawerContentType(nodeType); // Update content type
}, []);
  
const edgeTypes = { 
  custom: ({ sourceX, sourceY, targetX, targetY, source, target }) => {
    const sourceNode = nodes.find((node) => node.id === source);

    // Check if the source node is a trigger node
    const isTriggerEdge = sourceNode?.data?.type === "trigger";

    // Midpoint coordinates for positioning the PlusOutlined icon
    const midX = (sourceX + targetX) / 2;
    const midY = (sourceY + targetY) / 2;

      const edge = (
        <SmoothStepEdge
          sourceX={sourceX}
          sourceY={sourceY}
          targetX={targetX}
          targetY={targetY}
          source={source}
          target={target}
          style={{
            stroke: "#b1b1b7",
            strokeWidth: 2,
            pointerEvents: "none", // Prevent edge from blocking clicks
          }}
        />
      );

    // If the edge is for a trigger node, render only the edge
    if (isTriggerEdge) {
      return edge;
    }

    // Return the edge and the PlusOutlined icon
    return (
      <>
        {edge}
        <foreignObject
          x={midX - 12}
          y={midY - 12}
          width={24}
          height={24}
          style={{
            overflow: "visible",
            pointerEvents: "all", // Enable clicking on the icon
          }}
        >
          <div
            style={{
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              background: "#fff",
              border: "1px solid #ddd",
              borderRadius: "50%",
              width: "24px",
              position: 'relative',
              zindex: 999, /* Make sure parent stacking context allows the icon to appear in front */
              height: "24px",
              boxShadow: "0 2px 4px rgba(0, 0, 0, 0.1)",
              cursor: "pointer",
            }}
            onClick={() => handleAction()} // Handle add node
          >
              <PlusOutlined
               onClick={handleAction} // Open the drawer
               style={{
                fontSize: "14px",
                color: "black",
                position: "relative",
                right: "7px",
              }}
          />
          </div>
        </foreignObject>
      </>
    );
  },
};

  return (
   <div style={{ width: "100vw", height: "100vh" }}>
    <Row align="middle" style={{ padding: '16px',}}>
  {/* Back to Workflow Button */}
  <Col>
    <Button
      type="primary"
      icon={<ArrowLeftOutlined />}
      onClick={handleBackToWorkflow}
      style={{
        alignItems: 'center',
        padding: '0 12px',
        marginRight: '8px', // Adds a small space between the buttons
        display: 'flex',
      }}
    >
      Back To WorkFlow
    </Button>
  </Col>
</Row>

 <ReactFlow
        nodes={nodes}
        edges={edges.map((edge) => ({ ...edge, type: "custom" }))}
        onNodesChange={onNodesChange}
        onEdgesChange={onEdgesChange}
        onConnect={onConnect}
        edgeTypes={edgeTypes}
        onNodeClick={onNodeClick} // Attach the node click handler
      />
    <Drawer
        title={drawerContentType === "addNewTrigger" ? "Workflow Trigger" : "Node Details"}
        placement="right"
        onClose={() => setDrawerVisible(false)}
        open={drawerVisible}
        width={500}
      >
        {drawerContentType === "addNewTrigger"
          ? renderAddNewTriggerContent()
          : renderDefaultContent()}
      </Drawer>
       {/* New Drawer */}
       <Drawer
      title="Workflow Trigger"
      visible={isDrawerVisible}
      onClose={handleCloseDrawer}
      width={600}
      footer={
        <div style={{ textAlign: 'right' }}>
          <Button onClick={handleCloseDrawer} style={{ marginRight: '10px' }}>
            Cancel
          </Button>
          <Button type="primary" onClick={handleSave}>
            Save
          </Button>
        </div>
      }
    >
      <div>
        <h4>Choose a workflow Trigger</h4>
        <Input
          value={triggerName}
          onChange={(e) => setTriggerName(e.target.value)}
          placeholder="Choose a trigger"
          style={{ marginBottom: '20px' }}
        />

        <h4>Workflow Trigger Name</h4>
        <Input
          value={workflowTriggerName}
          onChange={(e) => setWorkflowTriggerName(e.target.value)}
          placeholder="Enter workflow trigger name"
          style={{ marginBottom: '20px' }}
        />

        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={handleAddFilter}
          style={{ marginBottom: '20px' }}
        >
          Add Filter
        </Button>

        {/* Render filter fields dynamically */}
        {filters.map((filter, index) => (
          <div key={index} style={{ display: 'flex', gap: '10px', marginBottom: '10px', alignItems: 'center' }}>
            <Select
              placeholder="Select an Operator"
              style={{ width: '50%' }}
              value={filter.operator}
              onChange={(value) => handleFilterChange(index, 'operator', value)}
            >
              <Option value="Select">Select</Option>
              <Option value="Ignored">Ignored</Option>
              <Option value="All">All</Option>
            </Select>
            <Input
              placeholder="Select a Customer"
              value={filter.customer}
              onChange={(e) => handleFilterChange(index, 'customer', e.target.value)}
              style={{ width: '50%' }}
            />
            {/* Delete Icon Button */}
            <Button
              type="text"
              icon={<DeleteOutlined />}
              onClick={() => handleDeleteFilter(index)}
              style={{ marginLeft: '10px' }}
            />
          </div>
        ))}
      </div>
    </Drawer>
    <Drawer
  title="Actions"
  visible={actionDrawer}
  onClose={handleActionDrawerClose}
  width={600}
>
  <Input.Search placeholder="Search actions..." style={{ marginBottom: 20 }} />
  <Button style={{ marginBottom: 20, width: "100%" }}>All Actions</Button>
  <h4>Contact</h4>
  {actions.map((action, index) => (
    <Button
      key={index}
      type="default"
      style={{
        marginBottom: "10px",
        width: "100%",
        textAlign: "left",
        padding: "10px 15px",
        display: "flex",
        alignItems: "center",
        border: "1px solid #d9d9d9",
        borderRadius: "6px",
        fontWeight: "500",
        backgroundColor: "#fff",
        color: "#333",
      }}
      onClick={() => handleActionDetail(action.name)} // Open the detail drawer
    >
      <span
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          width: "30px",
          color: "blue",
          fontSize: "18px",
        }}
      >
        {action.icon}
      </span>
      <span style={{ marginLeft: "10px", flex: 1 }}>{action.name}</span>
    </Button>
  ))}
</Drawer>

      <Drawer
  title={selectedAction}
  visible={detailDrawer}
  onClose={handleDetailDrawerClose}
  width={600}
>
  <h4>Action Name</h4>
  <Input value={selectedAction} disabled style={{ marginBottom: "20px" }} />
  
  <h4>Add Filters</h4>
  {filters.map((filter, index) => (
    <div
      key={index}
      style={{
        display: "flex",
        alignItems: "center",
        marginBottom: "10px",
        gap: "10px",
      }}
    >
      <Select
        placeholder="Select Operator"
        style={{ width: "45%" }}
        onChange={(value) =>
          setFilters((prev) => {
            const updated = [...prev];
            updated[index].operator = value;
            return updated;
          })
        }
      >
        <Option value="Select">Select</Option>
        <Option value="Ignored">Ignored</Option>
        <Option value="All">All</Option>
      </Select>
      <Input
        placeholder="Select Customer"
        style={{ width: "45%" }}
        onChange={(e) =>
          setFilters((prev) => {
            const updated = [...prev];
            updated[index].customer = e.target.value;
            return updated;
          })
        }
      />
      <Button
        type="text"
        danger
        onClick={() => handleDeleteFilter(index)}
      >
        Delete
      </Button>
    </div>
  ))}

  <Button
    type="dashed"
    onClick={handleAddFilter}
    style={{ width: "100%", marginBottom: "20px" }}
  >
    Add Filter
  </Button>

  <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px" }}>
    <Button onClick={handleSaveAction}>Cancel</Button>
    <Button type="primary">Save Action</Button>
  </div>
</Drawer>
 
</div>
  );
}


