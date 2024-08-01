// import React, { useState, useEffect } from "react";
// import { Space, Table, Input, Button, Modal, message, Card, Row, Col, Form } from "antd";
// import { SearchOutlined, EyeOutlined, UnorderedListOutlined, WindowsOutlined, DeleteOutlined, EditOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";
// // import { getCustomers, deleteCustomer } from "../../API";
// import { getDataFunction,  getProposals } from "../../../API";
// import { Link, useNavigate } from "react-router-dom";
// import Highlighter from "react-highlight-words";
// // import Payment from "../../Invoices/payment";

// function Payment () {
//   const [searchText, setSearchText] = useState({});
//   const [dataSource, setDataSource] = useState([]);
//   const [columns, setColumns] = useState([]);
//   const [loading, setLoading] = useState(false);
//   const [currentPage, setCurrentPage] = useState(1);
//   const [totalRecords, setTotalRecords] = useState(0);
//   const [deleteModalVisible, setDeleteModalVisible] = useState(false);
//   const [inviteModalVisible, setInviteModalVisible] = useState(false);
//   const [selectedPayment, setSelectedPayment] = useState(null);
//   const [viewMode, setViewMode] = useState('table'); // 'table' or 'card'
 
//   //  const [data,setData]=useState()
//   const navigate = useNavigate();

//   useEffect(() => {
//     fetchPayment(currentPage);
//   }, [currentPage]);

//   // Reset to page 1 when searchText changes
//   useEffect(() => {
//     fetchPayment(1);
//   }, [searchText]);

//   const fetchPayment = (page) => {
//     setLoading(true);
//     getDataFunction('payments',page)
//       .then((res) => {
//         console.log('resincome',res)
//         const sortedPayment = res.payments
        
//         // Filter dataSource based on searchText
//         const filteredPayment = sortedPayment.filter((payment) =>
//           Object.keys(searchText).every((key) => {
//             const paymentValue = key.includes('.')
//               ? key.split('.').reduce((obj, k) => (obj || {})[k], payment)
//               : payment[key];

//             return paymentValue && paymentValue.toString().toLowerCase().includes(searchText[key].toLowerCase());
//           })
//         );

//         setDataSource(filteredPayment);
//         setTotalRecords(res.pagination.total);

//         // Generate columns dynamically based on customer object keys
//         const generatedColumns = Object.keys(sortedPayment[0] || {}).map((key) => ({
//           title: (
//             <div>
//               {key}
//               <Input
//                 placeholder={`Search ${key}`}
//                 value={searchText[key] || ''}
//                 onChange={(e) => handleSearch(e.target.value, key)}
//                 style={{ marginTop: 8, display: 'block' }}
//               />
//             </div>
//           ),
//           dataIndex: key,
//           key: key,
//           render: (text) => {
//             return text; // Default rendering
//           }
//         }));

//         setColumns(generatedColumns);
//         setLoading(false);
//       })
//       .catch((error) => {
//         console.error('Error fetching payment:', error);
//         setLoading(false);
//       });
//   };

//   const handleSearch = (value, key) => {
//     const updatedSearchText = { ...searchText };
//     if (value === '') {
//       delete updatedSearchText[key];
//     } else {
//       updatedSearchText[key] = value;
//     }
//     setSearchText(updatedSearchText);
//   };

//   const handlePageChange = (page) => {
//     setCurrentPage(page);
//   };

//   const showDeleteModal = () => {
//     setDeleteModalVisible(true);
//   };


//   const handleDelete = () => {
//     deleteIncome(selectedIncome.id)
//       .then(() => {
//         message.success(`income deleted successfully!`);
//         fetchIncome(currentPage); // Refresh the customers list
//         setDeleteModalVisible(false);
//         setSelectedIncome(null);
//       })
//       .catch((error) => {
//         console.error('Failed to delete customer:', error);
//       });
//   };

//   const onRowClick = (record) => {
//     setSelectedPayment(selectedPayment && selectedPayment.id === record.id ? null : record);
//   };
  
//   // const renderCards = () => {
//   //   if (!dataSource || dataSource.length === 0) {
//   //     return <p>No customers available.</p>;
//   //   }

//   //   return (
//   //     <Row gutter={16}>
//   //       {dataSource.map((payment) => (
//   //         <Col key={payment.id} span={6}>
//   //           <Card
//   //             title={payment.name}
//   //             onClick={() => onRowClick(payment)}
//   //             style={{ cursor: 'pointer', backgroundColor: selectedPayment && selectedPayment.id === payment.id ? '#f0f0f0' : 'white' }}
//   //           >
//   //             {Object.keys(payment).map((key) => (
//   //               <p key={key}>
//   //                 <strong>{key}:</strong> {payment[key]}
//   //               </p>
//   //             ))}
//   //           </Card>
//   //         </Col>
//   //       ))}
//   //     </Row>
//   //   );
//   // };
//   //  const renderColumns = () => {
//   //   if (!columns || columns.length === 0) {
//   //     return null;
//   //   }

//   //   return columns.map((column) => ({
//   //     ...column,
//   //     render: (text, record) => ({
//   //       children: text,
//   //       props: {
//   //         style: {
//   //           backgroundColor: selectedPayment && selectedPayment.id === record.id ? '#f0f0f0' : 'white',
//   //           cursor: 'pointer',
//   //         },
//   //       },
//   //     }),
//   //   }));
//   // };
//   const renderColumns = () => {
//     if (!columns || columns.length === 0) {
//       return null;
//     }
  
//     return columns.map((column) => ({
//       ...column,
//       render: (text, record) => {
//         if (column.dataIndex === 'tags' && Array.isArray(text)) {
//           return (
//             <div>
//               {text.map((tag, index) => (
//                 <span key={index} style={{ background: tag.tag_color,
//                   margin:'2px',
//                   padding:'4px 8px 4px 8px',
//                   borderRadius:'15%',
//                   color:"white"  }}>
//                   {tag.tags_name}
//                 </span>
//               ))}
//             </div>
//           );
//         }
//         return text; // Default rendering for other columns
//       },
//     }));
//   };

//    const renderCards = () => {
//     if (!dataSource || dataSource.length === 0) {
//       return <p>No payments available.</p>;
//     }
  
//     return (
//       <Row gutter={16}>
//         {dataSource.map((payment) => (
//           <Col key={payment.id} span={6}>
//             <Card
//               title={payment.name}
//               onClick={() => onRowClick(payment)}
//               style={{
//                 cursor: 'pointer',
//                 backgroundColor: selectedPayment && selectedPayment.id === payment.id ? '#f0f0f0' : 'white',
//               }}
//             >
//               {Object.keys(payment).map((key) => (
//                 <p key={key}>
//                   <strong>{key}:</strong>{' '}
//                   {key === 'tags' && Array.isArray(payment[key]) ? (
//                     payment[key].map((tag, index) => (
//                       <span key={index} style={{ 
//                         margin: '2px',
//                         padding: '4px 8px',
//                         borderRadius: '15%',
//                         background: tag.tag_color,
//                         color: 'white'
//                       }}>
//                         {tag.tags_name}
//                       </span>
//                     ))
//                   ) : (
//                     payment[key]
//                   )}
//                 </p>
//               ))}
//             </Card>
//           </Col>
//         ))}
//       </Row>
//     );
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
//           <b style={{ fontSize: '18px', marginLeft: '18px' }}>Payment</b>
//         </div>

//         <div style={{ display: 'flex', fontSize: '18px', marginLeft: '18px', gap: '5px' }}>
//           {selectedPayment && (
//             <div style={{ gap: '2px' }}>
//               <Link to={`/payment/view/${selectedPayment.id}`}>
//                 <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
//                   <EyeOutlined />
//                 </Button>
//               </Link>
//               <Link to={`/payments/edit/${selectedPayment.id}`}>
//                 <Button type="link" style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
//                   <EditOutlined />
//                 </Button>
//               </Link>
//               <Button type="link" onClick={showDeleteModal} style={{ marginRight: '2px', border: '1px solid #ccc', background: 'white' }}>
//                 <DeleteOutlined />
//               </Button>
//             </div>
//           )}

        
//           <Link to="/payments/createform">
//             <Button type="primary" htmlType="button" icon={<PlusOutlined />} style={{ marginLeft: '10px', marginRight: '10px' }}>
//               Add
//             </Button>
//           </Link>
//           <Button
//             style={{ marginRight: '10px' }}
//             type={viewMode === 'table' ? 'primary' : 'default'}
//             onClick={() => setViewMode('table')}
//           >
//             <WindowsOutlined />
//           </Button>
//           <Button
//             type={viewMode === 'card' ? 'primary' : 'default'}
//             onClick={() => setViewMode('card')}
//           >
//             <UnorderedListOutlined />
//           </Button>
//         </div>
//       </div>
//       <div style={{ overflowX: 'scroll' }}>
//         {viewMode === 'table' ? (

//           <Table
//             className="datatable customers-table"
//             loading={loading}
//             columns={renderColumns()}
//             dataSource={dataSource}
//             pagination={{
//               current: currentPage,
//               pageSize: 10,
//               total: totalRecords,
//               onChange: handlePageChange,
//             }}
//             onRow={(record) => ({
//               onClick: () => onRowClick(record),
//               style: { cursor: 'pointer' },
//             })}
//           />
//         ) : (
//           renderCards()
//         )}
//         <h4>Total Payment: {totalRecords}</h4>
      
//         <Modal
//           title="Delete Confirmation"
//           open={deleteModalVisible}
//           onCancel={() => setDeleteModalVisible(false)}
//           onOk={handleDelete}
//           okText="Delete"
//           cancelText="Cancel"
//         >
//           Are you sure you want to delete the selected item?
//         </Modal>
//       </div>
//     </Space>
//   );
// }

// export default Payment;
