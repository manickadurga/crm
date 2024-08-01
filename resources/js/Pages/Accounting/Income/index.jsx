import React, { useState, useEffect } from "react";
import { Space, Table, Input, Button, Modal, message, Card, Row, Col, Form } from "antd";
import { SearchOutlined, EyeOutlined, UnorderedListOutlined, WindowsOutlined, DeleteOutlined, EditOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
// import { getCustomers, deleteCustomer } from "../../API";
import { getProposals } from "../../../API";
import { Link, useNavigate } from "react-router-dom";
import Highlighter from "react-highlight-words";

function Income () {
  const [searchText, setSearchText] = useState({});
  const [dataSource, setDataSource] = useState([]);
  const [columns, setColumns] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalRecords, setTotalRecords] = useState(0);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [inviteModalVisible, setInviteModalVisible] = useState(false);
  const [selectedIncome, setSelectedIncome] = useState(null);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
 
  //  const [data,setData]=useState()
  const navigate = useNavigate();

  useEffect(() => {
    fetchIncome(currentPage);
  }, [currentPage]);

  // Reset to page 1 when searchText changes
  useEffect(() => {
    fetchIncome(1);
  }, [searchText]);

  const fetchIncome = (page) => {
    setLoading(true);
    getProposals(page)
      .then((res) => {
        console.log('resincome',res)
        const sortedIncome = res.proposals
        
        // Filter dataSource based on searchText
        const filteredIncome = sortedIncome.filter((income) =>
          Object.keys(searchText).every((key) => {
            const incomeValue = key.includes('.')
              ? key.split('.').reduce((obj, k) => (obj || {})[k], income)
              : income[key];

            return incomeValue && incomeValue.toString().toLowerCase().includes(searchText[key].toLowerCase());
          })
        );

        setDataSource(filteredIncome);
        // setTotalRecords(res.pagination.total);

        // Generate columns dynamically based on customer object keys
        const generatedColumns = Object.keys(sortedIncome[0] || {}).map((key) => ({
          title: (
            <div>
              {key}
              <Input
                placeholder={`Search ${key}`}
                value={searchText[key] || ''}
                onChange={(e) => handleSearch(e.target.value, key)}
                style={{ marginTop: 8, display: 'block' }}
              />
            </div>
          ),
          dataIndex: key,
          key: key,
          render: (text) => {
            return text; // Default rendering
          }
        }));

        setColumns(generatedColumns);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching customers:', error);
        setLoading(false);
      });
  };

  const handleSearch = (value, key) => {
    const updatedSearchText = { ...searchText };
    if (value === '') {
      delete updatedSearchText[key];
    } else {
      updatedSearchText[key] = value;
    }
    setSearchText(updatedSearchText);
  };





  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  const showDeleteModal = () => {
    setDeleteModalVisible(true);
  };

  const showInviteModal = () => {
    setInviteModalVisible(true);
  };


  const handleOk = () => {
    console.log(formValues);
    setInviteModalVisible(false);
  };

  const handleDelete = () => {
    deleteIncome(selectedIncome.id)
      .then(() => {
        message.success(`income deleted successfully!`);
        fetchIncome(currentPage); // Refresh the customers list
        setDeleteModalVisible(false);
        setSelectedIncome(null);
      })
      .catch((error) => {
        console.error('Failed to delete customer:', error);
      });
  };

  const onRowClick = (record) => {
    setSelectedIncome(selectedIncome && selectedIncome.id === record.id ? null : record);
  };
  const renderCards = () => {
    if (!dataSource || dataSource.length === 0) {
      return <p>No customers available.</p>;
    }

    return (
      <Row gutter={16}>
        {dataSource.map((income) => (
          <Col key={income.id} span={6}>
            <Card
              title={income.name}
              onClick={() => onRowClick(income)}
              style={{ cursor: 'pointer', backgroundColor: selectedIncome && selectedIncome.id === income.id ? '#f0f0f0' : 'white' }}
            >
              {Object.keys(income).map((key) => (
                <p key={key}>
                  <strong>{key}:</strong> {income[key]}
                </p>
              ))}
            </Card>
          </Col>
        ))}
      </Row>
    );
  };
  const renderColumns = () => {
    if (!columns || columns.length === 0) {
      return null;
    }

    return columns.map((column) => ({
      ...column,
      render: (text, record) => ({
        children: text,
        props: {
          style: {
            backgroundColor: selectedIncome && selectedIncome.id === record.id ? '#f0f0f0' : 'white',
            cursor: 'pointer',
          },
        },
      }),
    }));
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
          <b style={{ fontSize: '18px', marginLeft: '18px' }}>Income {totalRecords}</b>
        </div>

        <div style={{ display: 'flex', fontSize: '18px', marginLeft: '18px', gap: '5px' }}>
          {selectedIncome && (
            <div style={{ gap: '2px' }}>
              <Link to={`/incom/view/${selectedIncome.id}`}>
                <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                  <EyeOutlined />
                </Button>
              </Link>
              <Link to={`/income/edit/${selectedIncome.id}`}>
                <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                  <EditOutlined />
                </Button>
              </Link>
              <Button type="link" onClick={showDeleteModal} style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
                <DeleteOutlined />
              </Button>
            </div>
          )}

          <Button type="primary" htmlType="button" onClick={showInviteModal}>
            Invite
          </Button>
          <Link to="/income/createform">
            <Button type="primary" htmlType="button" icon={<PlusOutlined />} style={{ marginLeft: '10px', marginRight: '10px' }}>
              Add
            </Button>
          </Link>
          <Button
            style={{ marginRight: '10px' }}
            type={viewMode === 'table' ? 'primary' : 'default'}
            onClick={() => setViewMode('table')}
          >
            <WindowsOutlined />
          </Button>
          <Button
            type={viewMode === 'card' ? 'primary' : 'default'}
            onClick={() => setViewMode('card')}
          >
            <UnorderedListOutlined />
          </Button>
        </div>
      </div>
      <div style={{ overflowX: 'scroll' }}>
        {viewMode === 'table' ? (

          <Table
            className="datatable customers-table"
            loading={loading}
            columns={renderColumns()}
            dataSource={dataSource}
            pagination={{
              current: currentPage,
              pageSize: 10,
              total: totalRecords,
              onChange: handlePageChange,
            }}
            onRow={(record) => ({
              onClick: () => onRowClick(record),
              style: { cursor: 'pointer' },
            })}
          />
        ) : (
          renderCards()
        )}
        <h4>Total Income: {totalRecords}</h4>
      
        <Modal
          title="Delete Confirmation"
          open={deleteModalVisible}
          onCancel={() => setDeleteModalVisible(false)}
          onOk={handleDelete}
          okText="Delete"
          cancelText="Cancel"
        >
          Are you sure you want to delete the selected item?
        </Modal>
      </div>
    </Space>
  );
}

export default Income;
