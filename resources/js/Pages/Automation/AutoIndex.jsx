import React from 'react';
import { Tabs, Button, Input, Row, Col, Menu, Table, Space } from 'antd';
import { FolderOutlined, PlusOutlined, SearchOutlined, FileTextOutlined, CheckCircleOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';

function AutoIndex() {
  const { TabPane } = Tabs;
  const navigate = useNavigate()

  const handleTabChange = (key) => {
    if (key === "2") {
      navigate("/workflow");
    }
  };
  
  const dataSource = [
    {
      name: 'Workflow 1',
      totalEnrolled: 100,
      activeEnrolled: 80,
      lastUpdated: '2024-09-15',
      credited: 'Yes',
    },
    {
      name: 'Workflow 2',
      totalEnrolled: 50,
      activeEnrolled: 45,
      lastUpdated: '2024-09-12',
      credited: 'No',
    },
  ];

  // Column configuration
  const columns = [
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: 'Total Enrolled',
      dataIndex: 'totalEnrolled',
      key: 'totalEnrolled',
    },
    {
      title: 'Active Enrolled',
      dataIndex: 'activeEnrolled',
      key: 'activeEnrolled',
    },
    {
      title: 'Last Updated',
      dataIndex: 'lastUpdated',
      key: 'lastUpdated',
    },
    {
      title: 'Credited',
      dataIndex: 'credited',
      key: 'credited',
    },
    {
      title: 'Actions',
      key: 'actions',
      render: () => (
        <Space size="middle">
          <Button><EditOutlined /></Button>
          <Button danger><DeleteOutlined /></Button>
        </Space>
      ),
    },
  ];
  
  return (
    <>
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
      <Col>
        <Tabs defaultActiveKey="1" onChange={handleTabChange}>
          <TabPane tab="Automation" key="1" />
          <TabPane tab="Workflows" key="2" />
        </Tabs>
      </Col>
    </Row>
      <Row justify="space-between" align="top">
        <Col span={6} style={{ textAlign: 'left' }}>
        <h2 style={{ fontWeight: 300, fontFamily: "'Poppins', sans-serif" }}>Workflows</h2>
         <h3 style={{ marginTop: 24 }}>All Workflows</h3>
          <Menu mode="vertical" style={{ marginTop: 16 }}>
            <Menu.Item key="draft" icon={<FileTextOutlined />}>
              Draft
            </Menu.Item>
            <Menu.Item key="published" icon={<CheckCircleOutlined />}>
              Published
            </Menu.Item>
          </Menu>
        </Col>
        <Col span={18} style={{ textAlign: 'right' }}>
          <Input
            placeholder="Search workflows and folders"
            prefix={<SearchOutlined />}
            style={{ width: 300, marginBottom: 16 }}
          />
          <Button icon={<FolderOutlined />} type="default" style={{ marginLeft: 16 }}>
            Create folder
          </Button>
          <Button icon={<PlusOutlined />} type="primary" style={{ marginLeft: 8 }}>
            Create workflow
          </Button>
         <Table
            dataSource={dataSource}
            columns={columns}
            style={{ marginTop: 24 }}
          />
        </Col>
      </Row>  
    </>
  );
}
export default AutoIndex;
