import React, { useState, useEffect } from 'react';
import { Button, Form, Input, Select, Checkbox, DatePicker, message, Tooltip } from 'antd';
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link, useParams, useNavigate } from "react-router-dom";
import { getFormfields } from '../../../API';
import axios from 'axios';
import dayjs from 'dayjs';

const { Option } = Select;

const formItemLayout = {
  labelCol: {
    xs: { span: 28 },
    sm: { span: 8 },
  },
  wrapperCol: {
    xs: { span: 24 },
    sm: { span: 16 },
  },
};

const tailFormItemLayout = {
  wrapperCol: {
    xs: { span: 24, offset: 0 },
    sm: { span: 16, offset: 8 },
  },
};

const gridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(2, 1fr)',
  gap: '16px',
};

const gridItemStyle = {
  width: '100%',
};
  
const PaymentForm = () => {
  const { id } = useParams();
  const [current, setCurrent] = useState(0);
  const [paymentFormFields, setPaymentFormFields] = useState([]);
  const [paymentData, setPaymentData] = useState(null);
  const [selectedPickDate, setSelectedPickDate] = useState(null);

  const [form] = Form.useForm();
  const navigate = useNavigate();

  // Fetch form fields
  useEffect(() => {
    getFormfields('Payments')
      .then((res) => {
        setPaymentFormFields(res);
      })
      .catch((error) => {
        console.error("Error fetching form fields:", error);
      });
  }, []);

  useEffect(() => {
    console.log("paymentFormFields state has been set:", paymentFormFields);
  }, [paymentFormFields]);

  useEffect(() => {
    // Fetch payment data by ID if editing existing payment
    const fetchPayment = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/api/payments/${id}`);
        console.log('Payment data fetched:', response.data);
        setPaymentData(response.data);
      } catch (error) {
        console.error('Error fetching payment:', error);
        if (error.response && error.response.status === 404) {
          console.error('Payment not found.');
        } else {
          console.error('An unexpected error occurred.');
        }
      }
    };
    if (id) fetchPayment();
  }, [id]);

  useEffect(() => {
    // Set form fields with fetched payment data when available
    if (paymentData) {
      console.log('Formatted payment data:', paymentData);
      form.setFieldsValue(paymentData);
    }
  }, [paymentData, form]);

  const onFinish = async (values) => {
    console.log('Received values from form:', values);
    
    // Extract selected option IDs from the form fields
    const selectedOptionIds = {};
    paymentFormFields.forEach(section => {
        section.fields.forEach(field => {
            if ((field.type === '16' || field.type === '33') && values[field.name]) {
                if (field.type === '16') {
                    // For single-select fields (type '16')
                    const selectedOption = field.options.find(option => option.label === values[field.name]);
                    if (selectedOption) {
                        selectedOptionIds[field.name] = selectedOption.id !== undefined ? selectedOption.id : values[field.name];
                    }
                } else if (field.type === '33') {
                    // For multi-select fields (type '33')
                    const selectedOptions = field.options.filter(option => values[field.name].includes(option.label));
                    const ids = selectedOptions.map(option => option.id);
                    selectedOptionIds[field.name] = ids;
                }
            }
        });
    });

    // Filter and map projects array to extract IDs
    // const projectIds = values.projects.filter(project => project !== undefined).map(project => project.id);
    
    // Prepare data to submit including selected option IDs and project IDs
    const dataToSubmit = {
        ...values,
        ...selectedOptionIds,
        // projects: projectIds, // Replace projects array with project IDs
    };
    console.log('Data to Submit:', dataToSubmit);

    // Determine URL and method
    const url = id ? `http://127.0.0.1:8000/api/payments/${id}` : 'http://127.0.0.1:8000/api/payments';
    const method = id ? 'put' : 'post';

    try {
        const response = await axios({ method, url, data: dataToSubmit });
        console.log(`${id ? 'Payment updated:' : 'Payment created:'}`, response.data);
        message.success(`${id ? 'Payment updated successfully!' : 'Payment created successfully!'}`);
        navigate('/payments'); // Navigate to payments list or show success message
    } catch (error) {
        console.error(`There was an error ${id ? 'updating' : 'creating'} the payment!`, error);
        message.error(`There was an error ${id ? 'updating' : 'creating'} the payment!`);
    }
};

  const steps = paymentFormFields.map((section, sectionIndex) => (
    <div key={section.title}>
      <h3>{section.title}</h3>
      <div style={gridStyle}>
        {section.fields.map((field, fieldIndex) => (
          <div key={fieldIndex} style={gridItemStyle}>
            <Form.Item
              name={field.name}
              label={field.label}
              rules={field.rules}
              className="form-item"
            >
              {field.type === '16' ? (
                <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                  {field.options.map((option) => (
                    <Select.Option key={option.id} value={option.label}>
                      {option.label}
                    </Select.Option>
                  ))}
                </Select>
              ) : field.type === '33' ? (
                <Select
                  mode="multiple"
                  style={{ width: '100%' }}
                  placeholder="Select tags"
                  defaultValue={field.defaultValue}
                >
                  {field.options.map((option) => (
                    <Option key={option.id} value={option.label}>
                      <span
                        style={{
                          display: 'flex',
                          alignItems: 'center'
                        }}
                      >
                        <span
                          aria-label={option.label}
                          className={`badge-${option.value}`}
                          style={{
                            display: 'block',
                            width: '13px',
                            height: '13px',
                            borderRadius: '50px',
                            marginRight: '2px',
                            backgroundColor: option.color, // Using the color from the options array
                          }}
                        ></span>
                        {option.label}
                      </span>
                    </Option>
                  ))}
                </Select>
              ) : field.type === '5' ? (
                <DatePicker
                  style={{ width: '100%' }}
                />
              ) : field.type === 'checkbox' ? (
                <Checkbox defaultChecked={field.value || field.defaultValue}>
                </Checkbox>
              ) : field.prefixDropdown ? (
                <Input
                  addonBefore={
                    <Select defaultValue={field.prefixOptionsValue || field.prefixOptions[0].value}>
                      {field.prefixOptions.map((option, optionIndex) => (
                        <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                      ))}
                    </Select>
                  }                                      
                                           
                  style={{ width: '100%' }}
                  defaultValue={field.value || field.defaultValue}
                />
              ) : field.suffixDropdown ? (
                <Input
                  addonAfter={
                    <Select defaultValue={field.suffixOptionsValue || field.suffixOptions[0].value}>
                      {field.suffixOptions.map((option, optionIndex) => (
                        <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                      ))}
                    </Select>
                  }
                  style={{ width: '100%' }}
                  defaultValue={field.value || field.defaultValue}
                />
              ) : field.type === 'textarea' ? (
                <Input.TextArea 
                  rows={4} placeholder="Notes" maxLength={6}
                />
              ) : (
                <Input
                  type={field.type}
                  style={{ width: '100%' }}
                  defaultValue={field.defaultValue}
                />
              )}
            </Form.Item>
          </div>
        ))}
      </div>
    </div>
  ));

  const next = () => {
    setCurrent(current + 1);
  };

  const prev = () => {
    setCurrent(current - 1);
  };

  return (
    <>
      <Link to="/payments">
        <Tooltip placement="right">
          <Button shape="circle" htmlType="button">
            <ArrowLeftOutlined />
          </Button>
        </Tooltip>
      </Link>
      <Form
        {...formItemLayout}
        form={form}
        name="paymentform"
        onFinish={onFinish}
        scrollToFirstError
      >
        {steps[current]}
        <div style={{ marginTop: 24 }}>
          {current < steps.length - 1 && (
            <Button type="primary" onClick={next}>
              Next
            </Button>
          )}
          {current === steps.length - 1 && (
            <Button type="primary" htmlType="submit">
              {id ? 'Update Payment' : 'Submit Payment'}
            </Button>
          )}
          {current > 0 && (
            <Button style={{ margin: '0 8px' }} onClick={prev}>
              Previous
            </Button>
          )}
        </div>
      </Form>
    </>
  );
};

export default PaymentForm;


//import React, { useState, useEffect } from 'react';
// import { Button, Form, Input, Select, Checkbox, DatePicker, message, Tooltip } from 'antd';
// import { ArrowLeftOutlined } from "@ant-design/icons";
// import { Link, useParams, useNavigate } from "react-router-dom";
// import { getFormfieldsPayments } from '../../../API';
// import axios from 'axios';
// import dayjs from 'dayjs';

// const { Option } = Select;

// const formItemLayout = {
//   labelCol: {
//     xs: { span: 28 },
//     sm: { span: 8 },
//   },
//   wrapperCol: {
//     xs: { span: 24 },
//     sm: { span: 16 },
//   },
// };

// const tailFormItemLayout = {
//   wrapperCol: {
//     xs: { span: 24, offset: 0 },
//     sm: { span: 16, offset: 8 },
//   },
// };

// const gridStyle = {
//   display: 'grid',
//   gridTemplateColumns: 'repeat(2, 1fr)',
//   gap: '16px',
// };

// const gridItemStyle = {
//   width: '100%',
// };

// const PaymentForm = () => {
//   const { id } = useParams();
//   const [current, setCurrent] = useState(0);
//   const [paymentFormFields, setPaymentFormFields] = useState([]);
//   const [paymentData, setPaymentData] = useState(null);
//   const [selectedPickDate, setSelectedPickDate] = useState(null);

//   const [form] = Form.useForm();
//   const navigate = useNavigate();

//   // Fetch form fields
//   useEffect(() => {
//     getFormfieldsPayments()
//       .then((res) => {
//         setPaymentFormFields(res);
//       })
//       .catch((error) => {
//         console.error("Error fetching form fields:", error);
//       });
//   }, []);

//   useEffect(() => {
//     console.log("paymentFormFields state has been set:", paymentFormFields);
//   }, [paymentFormFields]);

//   useEffect(() => {
//     // Fetch invoice data by ID
//     const fetchPayment = async () => {
//       try {
//         const response = await axios.get(`http://127.0.0.1:8000/api/payments/${id}`);
//         console.log('Invoice data fetched:', response.data);
//         setPaymentData(response.data);
//       } catch (error) {
//         console.error('Error fetching invoice:', error);
//         if (error.response && error.response.status === 404) {
//           console.error('Invoice not found.');
//         } else {
//           console.error('An unexpected error occurred.');
//         }
//       }
//     };
//     if (id) fetchPayment();
//   }, [id]);

//   useEffect(() => {
//     if (paymentData) {
//       console.log('Formatted payment data:', paymentData);
//       form.setFieldsValue(paymentData);
//     }
//   }, [paymentData, form]);

//   const extractSelectedOptionIds = (fields) => {
//     const selectedOptionIds = {};
//     fields.forEach((field) => {
//       if (field.type === '16' || field.type === '33') {
//         selectedOptionIds[field.name] = field.options.reduce((acc, option) => {
//           if (form.getFieldValue(field.name).includes(option.value)) {
//             acc.push(option.id);
//           }
//           return acc;
//         }, []);
//       }
//     });
//     return selectedOptionIds;
//   };

//   const onFinish = async (values) => {
//     console.log('Received values from form:', values);
    
//     // Extract selected option IDs
//     const selectedOptionIds = extractSelectedOptionIds(paymentFormFields);
//     console.log('Selected Option Ids:', selectedOptionIds);
    
//     // Prepare data to submit including selected option IDs
//     const dataToSubmit = {
//       ...values,
//       ...selectedOptionIds, // Include selected option IDs in the submission data
//     };
  
//     const url = id ? `http://127.0.0.1:8000/api/payments/${id}` : 'http://127.0.0.1:8000/api/payments';
//     const method = id ? 'put' : 'post';
  
//     try {
//       const response = await axios({ method, url, data: dataToSubmit });
//       console.log(`${id ? 'Payment updated:' : 'Payment created:'}`, response.data);
//       message.success(`${id ? 'Payment updated successfully!' : 'Payment created successfully!'}`);
//       navigate('/payments'); // Navigate to payments list or show success message
//     } catch (error) {
//       console.error(`There was an error ${id ? 'updating' : 'creating'} the payment!`, error);
//       // Handle error, show error message
//     }
//   };
  


  
//   const steps = paymentFormFields.map((section, sectionIndex) => (
//     <div key={section.title}>
//       <h3>{section.title}</h3>
//       <div style={gridStyle}>
//         {section.fields.map((field, fieldIndex) => (
//           <div key={fieldIndex} style={gridItemStyle}>
//             <Form.Item
//               name={field.name}
//               label={field.label}
//               rules={field.rules}
//               className="form-item"
//             >
//               {field.type === '16' ? (
//                 <Select style={{ width: '100%' }} defaultValue={field.defaultValue} id={field.id}>
//                   {field.options.map((option) => (
//                     <Select.Option key={option.id} value={option.value} id={option.id}>
//                       {option.label}
//                     </Select.Option>
//                   ))}
//                 </Select>
//               ) : field.type === '33' ? (
//                 <Select
//                   mode="multiple"
//                   style={{ width: '100%' }}
//                   placeholder="Select tags"
//                   defaultValue={field.defaultValue}
//                 >
//                   {field.options.map((option) => (
//                     <Option key={option.id} value={option.value} id={option.id}>
//                       <span
//                         style={{
//                           display: 'flex',
//                           alignItems: 'center'
//                         }}
//                       >
//                         <span
//                           aria-label={option.label}
//                           className={`badge-${option.value}`}
//                           style={{
//                             display: 'block',
//                             width: '13px',
//                             height: '13px',
//                             borderRadius: '50px',
//                             marginRight: '2px',
//                             backgroundColor: option.color, // Using the color from the options array
//                           }}
//                         ></span>
//                         {option.label}
//                       </span>
//                     </Option>
//                   ))}
//                 </Select>
//               ) : field.type === '5' ? (
//                 id ? (
//                   <DatePicker
//                     style={{ width: '100%' }}
//                     value={field.name === 'payment_date' ? selectedPickDate : ''}
//                     onChange={(date) => field.name === 'payment_date' ? setSelectedPickDate(date) : ''}
//                   />
//                 ) : (
//                   <DatePicker
//                     style={{ width: '100%' }}
//                   />
//                 )
//               ) : field.type === 'checkbox' ? (
//                 <Checkbox defaultChecked={field.value || field.defaultValue}>
//                 </Checkbox>
//               ) : field.prefixDropdown ? (
//                 <Input
//                   addonBefore={
//                     <Select defaultValue={field.prefixOptionsValue || field.prefixOptions[0].value}>
//                       {field.prefixOptions.map((option, optionIndex) => (
//                         <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                       ))}
//                     </Select>
//                   }
//                   style={{ width: '100%' }}
//                   defaultValue={field.value || field.defaultValue}
//                 />
//               ) : field.suffixDropdown ? (
//                 <Input
//                   addonAfter={
//                     <Select defaultValue={field.suffixOptionsValue || field.suffixOptions[0].value}>
//                       {field.suffixOptions.map((option, optionIndex) => (
//                         <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                       ))}
//                     </Select>
//                   }
//                   style={{ width: '100%' }}
//                   defaultValue={field.value || field.defaultValue}
//                 />
//               ) : field.type === 'textarea' ? (
//                 <Input.TextArea 
//                   rows={4} placeholder="Notes" maxLength={6}
//                 />
//               ) : (
//                 <Input
//                   type={field.type}
//                   style={{ width: '100%' }}
//                   defaultValue={field.defaultValue}
//                 />
//               )}
//             </Form.Item>
//           </div>
//         ))}
//       </div>
//     </div>
//   ));

//   const next = () => {
//     setCurrent(current + 1);
//   };

//   const prev = () => {
//     setCurrent(current - 1);
//   };

//   return (
//     <>
//       <Link to="/payments">
//         <Tooltip placement="right">
//           <Button shape="circle" htmlType="button">
//             <ArrowLeftOutlined />
//           </Button>
//         </Tooltip>
//       </Link>
//       <Form
//         {...formItemLayout}
//         form={form}
//         name="paymentform"
//         onFinish={onFinish}
//         scrollToFirstError
//       >
//         {steps[current]}
//         <div style={{ marginTop: 24 }}>
//           {current < steps.length - 1 && (
//             <Button type="primary" onClick={next}>
//               Next
//             </Button>
//           )}
//           {current === steps.length - 1 && (
//             <Button type="primary" htmlType="submit">
//               {id ? 'Update Payment' : 'Submit Payment'}
//             </Button>
//           )}
//           {current > 0 && (
//             <Button style={{ margin: '0 8px' }} onClick={prev}>
//               Previous
//             </Button>
//           )}
//         </div>
//       </Form>
//     </>
//   );
// };

// export default PaymentForm;



// import React, { useState, useEffect } from 'react';
// import { Button, Form, Input, Select, Checkbox, DatePicker, message, Tooltip } from 'antd';
// import { ArrowLeftOutlined } from "@ant-design/icons";
// import { Link, useParams, useNavigate } from "react-router-dom";
// import { getFormfieldsPayments } from '../../../API';
// import axios from 'axios';
// import dayjs from 'dayjs';

// const { Option } = Select;

// const formItemLayout = {
//   labelCol: {
//     xs: { span: 28 },
//     sm: { span: 8 },
//   },
//   wrapperCol: {
//     xs: { span: 24 },
//     sm: { span: 16 },
//   },
// };
// const fullgridStyle = {
//   display: 'grid',
//   gridTemplateColumns: '1fr',
// };

// const tailFormItemLayout = {
//   wrapperCol: {
//     xs: { span: 24, offset: 0 },
//     sm: { span: 16, offset: 8 },
//   },
// };

// const gridStyle = {
//   display: 'grid',
//   gridTemplateColumns: 'repeat(2, 1fr)',
//   gap: '16px',
// };

// const gridItemStyle = {
//   width: '100%',
// };

// const PaymentForm = () => {
//   const { id } = useParams();
//   const [current, setCurrent] = useState(0);
//   const [paymentFormFields, setPaymentFormFields] = useState([]);
//   const [paymentData, setPaymentData] = useState(null);
//   const [selectedPickDate, setSelectedPickDate] = useState(null);

//   const [form] = Form.useForm();
//   const navigate = useNavigate();

//   // Fetch form fields
//   useEffect(() => {
//     getFormfieldsPayments()
//       .then((res) => {
//         setPaymentFormFields(res);
//       })
//       .catch((error) => {
//         console.error("Error fetching form fields:", error);
//       });
//   }, []);

//   useEffect(() => {
//     console.log("paymentFormFields state has been set:", paymentFormFields);
//   }, [paymentFormFields]);

//   useEffect(() => {
//     // Fetch invoice data by ID
//     const fetchPayment = async () => {
//       try {
//         const response = await axios.get(`http://127.0.0.1:8000/api/payments/${id}`);
//         console.log('Invoice data fetched:', response.data);
//         setPaymentData(response.data);
//       } catch (error) {
//         console.error('Error fetching invoice:', error);
//         if (error.response && error.response.status === 404) {
//           console.error('Invoice not found.');
//         } else {
//           console.error('An unexpected error occurred.');
//         }
//       }
//     };
//     if (id) fetchPayment();
//   }, [id]);

//   useEffect(() => {
//     if (paymentData) {
//       console.log('Formatted payment data:', paymentData);
//       // const formattedData = {
//       //   ...paymentData,
//       //   payment_date: paymentData.payment_date ? dayjs(paymentData.payment_date) : null,
//       // };
//       // console.log('Formatted data for form:', formattedData);
//       form.setFieldsValue(paymentData);
// console.log("s", form.setFieldsValue(paymentData) );

//     }
//   }, [paymentData, form]);

  
//   const onFinish = async (values) => {
//     console.log('Received values from form:', values);
//     const url = id ? `http://127.0.0.1:8000/api/payments/${id}` : 'http://127.0.0.1:8000/api/payments';
//     const method = id ? 'put' : 'post';

//     try {
//       const response = await axios({ method, url, data: values });
//       console.log(`${id ? 'Payment updated:' : 'Payment created:'}`, response.data);
//       message.success(`${id ? 'Payment updated successfully!' : 'Payment created successfully!'}`);
//       navigate('/payments'); // Navigate to payments list or show success message
//     } catch (error) {
//       console.error(`There was an error ${id ? 'updating' : 'creating'} the payment!`, error);
//       // Handle error, show error message
//     }
//   };

//   const steps = paymentFormFields.map((section, sectionIndex) => (
//     <div key={section.title}>
//       <h3>{section.title}</h3>
//       <div style={gridStyle}>
//         {section.fields.map((field, fieldIndex) => (
//           <div key={fieldIndex} style={gridItemStyle}>
//             <Form.Item
//               name={field.name}
//               label={field.label}
//               rules={field.rules}
//               className="form-item"
//             >
//               {field.type === '16' ? (
//                 <Select style={{ width: '100%' }} defaultValue={field.defaultValue} id={field.id}>
//                   {field.options.map((option, optionIndex) => (
//                     <Select.Option key={option.id} value={option.value} id={option.id}>
//                      {/* {option.id} */}
//                      {option.label}
//                     </Select.Option>
//                   ))}
//                 </Select>
//               ) : field.type === '33' ? (
//                 <Select
//                   mode="multiple"
//                   style={{ width: '100%' }}
//                   placeholder="Select tags"
//                   defaultValue={field.defaultValue}
//                 >
//                   {field.options.map((option, optionIndex) => (
//                     <Option key={optionIndex} value={option.value}>
//                       <span
//                         style={{
//                           display: 'flex',
//                           alignItems: 'center'
//                         }}
//                       >
//                         <span
//                           aria-label={option.label}
//                           className={`badge-${option.value}`}
//                           style={{
//                             display: 'block',
//                             width: '13px',
//                             height: '13px',
//                             borderRadius: '50px',
//                             marginRight: '2px',
//                             backgroundColor: option.color, // Using the color from the options array
//                           }}
//                         ></span>
//                         {option.label}
//                       </span>
//                     </Option>
//                   ))}
//                 </Select>
//               ) : field.type === '5' ? (
//                 id ? (
//                   <DatePicker
//                     style={{ width: '100%' }}
//                     value={field.name === 'payment_date' ? selectedPickDate : ''}
//                     onChange={(date) => field.name === 'payment_date' ? setSelectedPickDate(date) : ''}
//                   />
//                 ) : (
//                   <DatePicker
//                     style={{ width: '100%' }}
//                   />
//                 )
//               ) : field.type === 'checkbox' ? (
//                 <Checkbox defaultChecked={field.value || field.defaultValue}>
//                 </Checkbox>
//               ) : field.prefixDropdown ? (
//                 <Input
//                   addonBefore={
//                     <Select defaultValue={field.prefixOptionsValue || field.prefixOptions[0].value}>
//                       {field.prefixOptions.map((option, optionIndex) => (
//                         <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                       ))}
//                     </Select>
//                   }
//                   style={{ width: '100%' }}
//                   defaultValue={field.value || field.defaultValue}
//                 />
//               ) : field.suffixDropdown ? (
//                 <Input
//                   addonAfter={
//                     <Select defaultValue={field.suffixOptionsValue || field.suffixOptions[0].value}>
//                       {field.suffixOptions.map((option, optionIndex) => (
//                         <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                       ))}
//                     </Select>
//                   }
//                   style={{ width: '100%' }}
//                   defaultValue={field.value || field.defaultValue}
//                 />
//               ) : field.type === 'textarea' ? (
//                 <Input.TextArea 
//                   rows={4} placeholder="Notes" maxLength={6}
//                 />
//               ) : (
//                 <Input
//                   type={field.type}
//                   style={{ width: '100%' }}
//                   defaultValue={field.defaultValue}
//                 />
//               )}
//             </Form.Item>
//           </div>
//         ))}
//       </div>
//     </div>
//   ));

//   const next = () => {
//     setCurrent(current + 1);
//   };

//   const prev = () => {
//     setCurrent(current - 1);
//   };

//   return (
//     <>
//       <Link to="/payments">
//         <Tooltip  placement="right">
//           <Button shape="circle" htmlType="button">
//             <ArrowLeftOutlined />
//           </Button>
//         </Tooltip>
//       </Link>
//       <Form
//         {...formItemLayout}
//         form={form}
//         name="paymentform"
//         onFinish={onFinish}
//         scrollToFirstError
//       >
//         {steps[current]}
//         <div style={{ marginTop: 24 }}>
//           {current < steps.length - 1 && (
//             <Button type="primary" onClick={next}>
//               Next
//             </Button>
//           )}
//           {current === steps.length - 1 && (
//             <Button type="primary" htmlType="submit">
//               {id ? 'Update' : 'Create'}
//             </Button>
//           )}
//           {current > 0 && (
//             <Button style={{ margin: '0 8px' }} onClick={prev}>
//               Previous
//             </Button>
//           )}
//         </div>
//       </Form>
//     </>
//   );
// };

// export default PaymentForm;

