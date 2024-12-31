import React, { useState } from 'react';
import { BaseEdge, EdgeLabelRenderer, getStraightPath } from '@xyflow/react';
import { PlusOutlined, UploadOutlined, EditOutlined, UnorderedListOutlined } from '@ant-design/icons';
import { Drawer, Button, Input, Form, Select, Menu, Dropdown } from 'antd';
import ReactQuill from 'react-quill';
import axios from 'axios';

export default function CustomEdge({
  id, 
  sourceX, 
  sourceY, 
  targetX, 
  targetY,
}) {
  const [emailActionDrawerVisible, setEmailActionDrawerVisible] = useState(false);
  const [formData, setFormData] = useState({
    action_name: 'Email-Action',
    from_name: 'Support',
    from_email: 'sukasini2002@gmail.com',
    subject: 'Welcome to our Service!',
    content: '',
    attachments: [],
    testEmail: '',
  });
  const [filePath, setFilePath] = useState(null); // File upload handler
  const [form] = Form.useForm(); // Ant Design form management
  const [nodes,setNodes] =useState([])

  const handleFileChange = (event) => {
    const file = event.target.files[0];
    setFilePath(file ? file.name : null);
    setFormData({ ...formData, attachments: [file] });
  };

  const handleButtonClick = () => {
    document.getElementById('fileInput').click();
  };

  const handleInputChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
  };

  const openEmailActionDrawer = () => setEmailActionDrawerVisible(true);
  const closeEmailActionDrawer = () => setEmailActionDrawerVisible(false);

  const handleSaveAction = () => {
    const nodeId = Date.now().toString(); // Unique ID for the node

    const actionData = {
      action_name: formData.action_name,
      type: 'send_email',
      action_data: {
        from_name: formData.from_name,
        from_email: formData.from_email,
        subject: formData.subject,
        message: formData.content,
        attachments: formData.attachments,
      },
    };

    // Create a new node with the action data
    const newNode = {
      id: nodeId,
      data: {
        label: (
          <div>
            {actionData.action_name}
            <Dropdown
              overlay={
                <Menu>
                  <Menu.Item key="edit">Edit</Menu.Item>
                  <Menu.Item key="delete">Delete</Menu.Item>
                </Menu>
              }
              trigger={['click']}
            >
              <UnorderedListOutlined style={{ float: 'right', cursor: 'pointer' }} />
            </Dropdown>
          </div>
        ),
        type: 'action',
        formData: actionData,
      },
      position: { x: Math.random() * 250, y: Math.random() * 250 }, // Random position
    };

    // Update the nodes state with the new node
    setNodes((prevNodes) => [...prevNodes, newNode]);

    // Optionally send action data to an API
    sendActionDataToAPI(actionData);

    // Reset the form after saving
    form.resetFields();
    setFormData({
      action_name: 'Email-Action',
      from_name: 'Support',
      from_email: 'sukasini2002@gmail.com',
      subject: 'Welcome to our Service!',
      content: '',
      attachments: [],
      testEmail: '',
    });

    // Close the drawer after saving
    closeEmailActionDrawer();
  };

  // Function to send action data to the API
  const sendActionDataToAPI = (actionData) => {
    const apiUrl = 'http://127.0.0.1:8000/api/actions'; // API URL

    axios.post(apiUrl, actionData, {
      headers: {
        'Content-Type': 'application/json',
      },
    })
    .then((response) => {
      console.log('Successfully sent action data:', response.data);
    })
    .catch((error) => {
      console.error('Error sending action data:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
    });
  };

  // Get the straight path for the edge and label position
  const [edgePath, labelX, labelY] = getStraightPath({
    sourceX,
    sourceY,
    targetX,
    targetY,
  });

  return (
    <>
      <BaseEdge id={id} path={edgePath} />
      <EdgeLabelRenderer>
        <button
          style={{
            position: 'absolute',
            transform: `translate(-50%, -50%) translate(${labelX}px,${labelY}px)`,
            pointerEvents: 'all',
          }}
          className="nodrag nopan"
          onClick={openEmailActionDrawer} // Open the drawer when clicked
        >
          <PlusOutlined />  
        </button>
      </EdgeLabelRenderer>

      {/* Drawer for email action */}
      <Drawer
        title="Email Action"
        placement="right"
        visible={emailActionDrawerVisible}
        onClose={closeEmailActionDrawer}
      >
        <Form
          layout="vertical"
          form={form}
          onFinish={handleSaveAction} // Trigger handleSaveAction on form submit
        >
          <Form.Item
            label="Action Name"
            name="action_name"
            initialValue="Email"
            rules={[{ required: true, message: 'Please input the action name!' }]}
          >
            <Input onChange={(e) => handleInputChange('action_name', e.target.value)} />
          </Form.Item>

          <Form.Item
            label="From Name"
            name="from_name"
            rules={[{ required: true, message: 'Please input your name!' }]}
          >
            <Input
              value={formData.from_name}
              onChange={(e) => handleInputChange('from_name', e.target.value)}
              suffix={<EditOutlined style={{ cursor: 'pointer' }} />}
            />
          </Form.Item>

          <Form.Item
            label="From Email"
            name="from_email"
            rules={[{ required: true, type: 'email', message: 'Please input a valid email!' }]}
          >
            <Input
              value={formData.from_email}
              onChange={(e) => handleInputChange('from_email', e.target.value)}
              suffix={<EditOutlined style={{ cursor: 'pointer' }} />}
            />
          </Form.Item>

          <Form.Item
            label="Subject"
            name="subject"
            rules={[{ required: true, message: 'Please input the subject!' }]}
          >
            <Input
              value={formData.subject}
              onChange={(e) => handleInputChange('subject', e.target.value)}
              suffix={<EditOutlined style={{ cursor: 'pointer' }} />}
            />
          </Form.Item>

          <Form.Item label="Templates">
            <Select
              value={formData.template}
              onChange={(value) => handleInputChange('template', value)}
              style={{ width: '100%' }}
            >
              <Select.Option value="email">Email</Select.Option>
              <Select.Option value="sms">SMS</Select.Option>
              <Select.Option value="call">Call</Select.Option>
              <Select.Option value="whatsapp">WhatsApp</Select.Option>
            </Select>
          </Form.Item>

          <Form.Item label="Message">
            <ReactQuill
              value={formData.content}
              onChange={(value) => handleInputChange('content', value)}
            />
          </Form.Item>

          <input
            type="file"
            id="fileInput"
            style={{ display: 'none' }} // Hide the input element
            onChange={handleFileChange} // Trigger file selection handler
          />

          <Button icon={<UploadOutlined />} onClick={handleButtonClick}>
            Add Attachment
          </Button>
          {filePath && <p>Selected File: {filePath}</p>}

          <Form.Item label="Test Mail">
            <Input
              placeholder="Enter test email"
              value={formData.testEmail}
              onChange={(e) => handleInputChange('testEmail', e.target.value)}
            />
          </Form.Item>

          <Button type="primary" htmlType="submit">
            Save Action
          </Button>
        </Form>
      </Drawer>
    </>
  );
}
