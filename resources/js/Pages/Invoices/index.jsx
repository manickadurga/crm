import React, { useState, useEffect } from "react";
import {
  Space,
  Table,
  Button,
  Modal,
  Collapse,
  Input,
  Select,
  DatePicker,
  message,
  List,
  Avatar,
} from "antd";
import {
  EyeOutlined,
  EditOutlined,
  DeleteOutlined,
  ArrowLeftOutlined,
  PlusOutlined,
} from "@ant-design/icons";
import { getInvoices, deleteInvoice  } from "../../API";
import { Link } from "react-router-dom";
// import Updateform from "./Invoices/Updateform";
// import { UpdateForm } from "../../Pages/Invoices/Updateform";
import UpdateInvoices from "./UpdateInvoices";

const { Panel } = Collapse;
const { Option } = Select;
const { RangePicker } = DatePicker;

const selectOptions = [
  {
    title: "Tags",
    dataIndex: "tags",
    options: ["none", "urgent", "important", "pending", "completed"],
  },
  {
    title: "Paid Status",
    dataIndex: "paid",
    options: ["none", "paid", "unpaid"],
  },
  {
    title: "Status",
    dataIndex: "status",
    options: ["none", "inprogress", "pending", "completed"],
  },
];

function Invoices() {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState([]);
  const [searchText, setSearchText] = useState("");
  // const [selectedRows, setSelectedRows] = useState([]);
  // const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [comments, setComments] = useState([]);
  const [deleteModalVisible, setDeleteModalVisible] = useState(false);
  const [selectedRecord, setSelectedRecord] = useState(null);
  const [updateModalVisible, setUpdateModalVisible] = useState(false);
  const [updatedRecord, setUpdatedRecord] = useState(null);
  const [viewModalVisible, setViewModalVisible] = useState(false);

  useEffect(() => {
    setLoading(true);
    getInvoices().then((res) => {
      setDataSource(res.invoice.map((item, index) => ({ ...item, key: index.toString() })));
      setLoading(false);
    });
  }, []);

  

  

  const handleSearch = (value) => {
    setSearchText(value);
  };
  
  const filteredData = dataSource.filter((record) => 
     Object.values(record).some((value) =>
      value.toString().toLowerCase().includes(searchText.toLowerCase())
    )
  );
  // const handleCancelUpdate = () => {
  //   // Reset the updated record and close the update modal
  //   setUpdatedRecord(null);
  //   setUpdateModalVisible(false);
  // };
  // const updateRecord = (updatedRecord) => {
  //   // Logic to update the record
  //   console.log("Updated Record:", updatedRecord);
  //   setUpdateModalVisible(false);
  // };
  const handleView = (record) => {
    setSelectedRecord(record);
    setViewModalVisible(true);
  };

  const showDeleteModal = (record) => {
    console.log("Delete:", record);
    setSelectedRecord(record); 
    setDeleteModalVisible(true);
  };

  const handleDelete = async () => {
    if (!selectedRecord || !selectedRecord.id) {
      console.error("No selected record to delete");
      return;
    }
  
    try {
      await deleteInvoice(selectedRecord.id);
      setDataSource(dataSource.filter((item) => item.id !== selectedRecord.id));
      message.success("Invoice deleted successfully");
    } catch (error) {
      console.error("Error deleting Invoice:", error);
      message.error("Failed to delete Invoice");
    } finally {
      setDeleteModalVisible(false);
      setSelectedRecord(null);
    }
  };
  
  
  // Example function to select a record
  const handleSelectRecord = (record) => {
    setSelectedRecord(record);
  };

  const handleUpdate = (record) => {
    setSelectedRecord(record);
    setUpdateModalVisible(true);
  };

  const handleCloseUpdate = () => {
    setUpdateModalVisible(false);
    setSelectedRecord(null);
  }
  const columns = [
    { title: "Invoice Number", dataIndex: "invoicenumber", key: "invoicenumber" },
    { title: "Contacts", dataIndex: "contacts", key: "contacts" },
    { title: "Invoice Date", dataIndex: "invoicedate", key: "invoicedate" },
    { title: "Due Date", dataIndex: "duedate", key: "duedate" },
    { title: "Discount", dataIndex: "discount", key: "discount" },
    { title: "Currency", dataIndex: "currency", key: "currency" },
    { title: "Terms", dataIndex: "terms", key: "terms" },
    { title: "Tags", dataIndex: "tags", key: "tags" },
    { title: "Tax1", dataIndex: "tax1", key: "tax1" },
    { title: "Tax2", dataIndex: "tax2", key: "tax2" },
    { title: "Apply Discount", dataIndex: "applydiscount", key: "applydiscount" },
    {
      title: "Action",
      key: "action",
      fixed: "right",
      render: (_, record) => (
        <Space size="middle">
           {/* <Button type="primary" onClick={() => handleView(record)}>
            <EyeOutlined />
          </Button> */}
          <Link to={`/invoices/view/${record.id}`}>
          <Button type="primary" htmlType="button" onClick={() => handleUpdate(record)}>
          <EditOutlined />
          </Button>
        </Link>
          <Button type="danger" onClick={() => showDeleteModal(record)}>
            <DeleteOutlined />
          </Button>
          {/* <Button type="primary" onClick={() => handleUpdate(record)}>
            <EditOutlined />
          </Button> */}
        </Space>
      ),
    },
  ];
  {updateModalVisible && (
    <UpdateInvoices record={selectedRecord} onClose={handleCloseUpdate} />
  )}
 


  return (
    <Space size={20} direction="vertical" style={{ width: "100%" }}>
      <div style={{ position: "sticky", display: "flex", justifyContent: "space-between", gap: "1rem" }}>
        <div style={{ display: "flex", alignItems: "center" }}>
          <Link to="/">
            <Button shape="circle" htmlType="button" size="small">
              <ArrowLeftOutlined />
            </Button>
          </Link>
          <b style={{ fontSize: "18px", marginLeft: "18px" }}>Invoices</b>
        </div>
        <Link to="/invoices/createform">
          <Button type="primary" htmlType="button" icon={<PlusOutlined />}>
            Create
          </Button>
        </Link>
      </div>
      <Collapse bordered={false} style={{ backgroundColor: "#fff" }}>
        <Panel header="Advanced Search" key="1">
          <div style={{ display: "flex", flexWrap: "wrap", gap: "8px", rowGap: "8px" }}>
            {columns.slice(0, -1).map((column, index) => (
              <Input
                key={index}
                placeholder={`Search ${column.title}`}
                onChange={(e) => handleSearch(e.target.value)}
                style={{ maxWidth: "240px" }}
              />
            ))}
            <RangePicker
              style={{ maxWidth: "240px" }}
              onChange={(dates, dateStrings) => handleSearch(dateStrings)}
            />
            <RangePicker
              style={{ maxWidth: "240px" }}
              onChange={(dates, dateStrings) => handleSearch(dateStrings)}
            />
            {selectOptions.map((select, index) => (
              <Select
                key={index}
                placeholder={`Filter by ${select.title}`}
                style={{ maxWidth: "240px" }}
                onChange={(value) => handleSearch(value)}
              >
                {select.options.map((option, optionIndex) => (
                  <Option key={optionIndex} value={option}>
                    {option}
                  </Option>
                ))}
              </Select>
            ))}
          </div>
        </Panel>
        <Panel header="History" key="2">
          <div style={{ display: "flex", gap: "16px" }}>
            <div style={{ width: "40%" }}>
              <Input type="text" placeholder="Title" style={{ marginBottom: "10px" }} />
              <Input.TextArea placeholder="Description" />
              <Button type="primary" style={{ marginTop: "10px" }}>
                Add Comment
              </Button>
            </div>
            <div style={{ width: "60%" }}>
              <h5 style={{ margin: 0 }}>Comments</h5>
              <List
                style={{ maxHeight: "158px", overflowY: "scroll" }}
                itemLayout="horizontal"
                dataSource={comments}
                renderItem={(item) => (
                  <List.Item style={{ display: "flex", flexDirection: "column" }}>
                    <List.Item.Meta
                      avatar={<Avatar src={item.avatar} />}
                      title={
                        <div>
                          <span style={{ marginRight: "8px", fontSize: "12px" }}>{item.name}</span>
                          <i style={{ color: "#888", fontSize: "11px" }}>{item.dateTime}</i>
                        </div>
                      }
                      style={{ width: "100%" }}
                    />
                    <div style={{ width: "100%", textIndent: "3rem" }}>
                      <b>{item.commentTitle}</b> &nbsp;<i>{item.commentDescription}</i>
                    </div>
                  </List.Item>
                )}
              />
            </div>
          </div>
        </Panel>
      </Collapse>
      <div style={{ overflowX: "scroll" }}>
        <Table
          className="datatable invoices-table"
          loading={loading}
          columns={columns}
          dataSource={filteredData}
          scroll={{ x: true, y: 340 }}
          pagination={{ pageSize: 8 }}
          // rowSelection={{
          //   type: "checkbox",
          //   selectedRowKeys,
          //   onChange: (selectedRowKeys, selectedRows) => {
          //     setSelectedRowKeys(selectedRowKeys);
          //     setSelectedRows(selectedRows);
          //   },
          // }}
        />
         
         {/* <List
        dataSource={dataSource}
        renderItem={item => (
          <List.Item
            onClick={() => openDeleteModal(item)}
          >
            {item.name}
          </List.Item>
        )}
      /> */}
        <Modal
          title="Delete Confirmation"
          visible={deleteModalVisible}
          onCancel={() => setDeleteModalVisible(false)}
          onOk={handleDelete}
          dataSource={dataSource}
          onRow={(record) => ({
            onClick: () => handleSelectRecord(record),
          })}
          okText="Delete"
          cancelText="Cancel"
        >
          Are you sure you want to delete the selected item(s)?
        </Modal>
        
        {/* <Modal
          title="View Details"
          open={viewModalVisible} // Use 'visible' instead of 'open'
          onCancel={() => setViewModalVisible(false)}
          footer={null}
          > */}
        
          {/* <div>
          {selectedRecord && (
          <>
          <p><strong>InvoiceNumber:</strong> {selectedRecord.invoicenumber}</p>
          <p><strong>Contact:</strong> {selectedRecord.contacts}</p>
          <p><strong>InvoiceDate:</strong> {selectedRecord.invoicedate}</p>
          <p><strong>DueDate:</strong> {selectedRecord.duedate}</p>
          <p><strong>Discount:</strong> {selectedRecord.discount}</p>
          <p><strong>Currency:</strong> {selectedRecord.currency}</p>
          <p><strong> Terms:</strong> {selectedRecord.terms}</p>
          <p><strong>Tags:</strong> {selectedRecord && selectedRecord.tags && selectedRecord.tags.map(tag => (
      <span key={tag}>{tag}, </span>
    ))}</p>
          <p><strong>Tax1:</strong> {selectedRecord.tax1}</p>
          <p><strong>Tax2:</strong> {selectedRecord.tax2}</p>
          <p><strong>ApplyDiscount:</strong> {selectedRecord.applydiscount}</p>
</>
)}
      </div> */}
      {/* </Modal> */}
      </div>
    </Space>
  );
};

export default Invoices;
