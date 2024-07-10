import {
  AutoComplete,
  Button,
  Cascader,
  Checkbox,
  Col,
  Form,
  Input,
  InputNumber,
  Row,
  Select,
  Alert,
  DatePicker,
  Dropdown,
  Steps,
  Space,
  Tooltip,
  Table

} from 'antd';
import React, { useState } from 'react';
import { SearchOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";

import { Link } from "react-router-dom";
import EditableTable from './editableTable';

const { Option } = Select;
const residences = [
  {
    value: 'zhejiang',
    label: 'Zhejiang',
    children: [
      {
        value: 'hangzhou',
        label: 'Hangzhou',
        children: [
          {
            value: 'xihu',
            label: 'West Lake',
          },
        ],
      },
    ],
  },
  {
    value: 'jiangsu',
    label: 'Jiangsu',
    children: [
      {
        value: 'nanjing',
        label: 'Nanjing',
        children: [
          {
            value: 'zhonghuamen',
            label: 'Zhong Hua Men',
          },
        ],
      },
    ],
  },
];
const formItemLayout = {
  labelCol: {
    xs: {
      span: 28,
    },
    sm: {
      span: 8,
    },
  },
  wrapperCol: {
    xs: {
      span: 24,
    },
    sm: {
      span: 16,
    },
  },
};
const tailFormItemLayout = {
  wrapperCol: {
    xs: {
      span: 24,
      offset: 0,
    },
    sm: {
      span: 16,
      offset: 8,
    },
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

const formFields = [
  {
    title: "Add Invoice",
    fields: [
      {
        name: 'invoicenumber',
        label: 'Invoice Number',
        type: 'number',
        rules: [
          {
            required: true,
            message: 'Enter your Invoice Number!',
          },
        ],
      },
      {
        name: 'contacts',
        label: 'Contacts',
        rules: [
          {
            required: true,
            message: 'Please select an Owner!',
          },
        ],
        type: 'dropdown',
        options: [
          { label: 'Zohodemo', value: 'zohodemo' },
          { label: 'Quickbooksdemo', value: 'quickbooksdemo' },
          { label: 'Mailchimpdemo', value: 'mailchimpdemo' },
        ],
        defaultValue: 'All Contacts',
      },
      {
        name: 'invoicedate',
        label: 'Invoice Date',
        rules: [
          {
            required: true,
            message: 'Please select your INV Date!',
          },
        ],
        type: 'datepicker',
      },
      {
        name: 'duedate',
        label: 'Due Date',
        rules: [
          {
            required: true,
            message: 'Please select your Due Date!',
          },
        ],
        type: 'datepicker',
      },
      {
        name: 'discount',
        label: 'Discount',
        rules: [
          {
            type: 'text',
            message: 'The input is not valid!',
          },
          {
            required: true,
            message: 'Enter your Discount!',
          },
        ],
        type: 'number',
        suffixDropdown: true,
        suffixOptions: [
          { label: '%', value: '%' },
          { label: 'flat', value: 'Flat' },
        ],
        prefixOptionsValue: '%',
        defaultValue: '20',
      },
      {
        name: 'currency',
        label: 'Currency',
        rules: [
          {
            required: true,
            message: 'Please select an Currency!',
          },
        ],
        type: 'dropdown',
        options: [
          { label: 'None', value: 'none' },
          { label: 'Currency 1', value: 'Currency1' },
          { label: 'Currency 2', value: 'Currency2' },
          { label: 'Currency 3', value: 'Currency3' },
        ],
        defaultValue: 'none',
        value: 'Lead 1'
      },
      {
        name: "terms",
        label: "Terms",
        type: "textarea"
      },
      {
        name: "tags",
        label: "Tags",
        type: "tagfields",
        tagOptions: [  
          {
            label: 'Urgent',
            value: 'urgent',
          },
          {
            label: 'Important',
            value: 'important',
          },
          {
            label: 'Pending',
            value: 'pending',
          },
          {
            label: 'Completed',
            value: 'completed',
          },
          {
            label: 'Paid',
            value: 'paid',
          },
        ],
        value: ['important', 'pending']
      },
      {
        name: 'tax1',
        label: 'Tax 1',
        type: 'number',
        suffixDropdown: true,
        suffixOptions: [
          { label: '%', value: '%' },
          { label: 'flat', value: 'Flat' },
        ],
        prefixOptionsValue: '%',
        defaultValue: '20',
      },
      {
        name: 'tax2',
        label: 'Tax 2',
        type: 'number',
        suffixDropdown: true,
        suffixOptions: [
          { label: '%', value: '%' },
          { label: 'flat', value: 'Flat' },
        ],
        prefixOptionsValue: '%',
        defaultValue: '20',
      },
      {
        name: 'applydiscount',
        label: 'Apply discount',
        type: 'checkbox',
        defaultValue: true,
      },
    ],
  },
];



const steps = formFields.map((section, sectionIndex) => (
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
            // {...(field.type !== 'checkbox' ? {label: field.label } : {})}
          >
            {field.type === 'dropdown' ? (
              <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                {field.options.map((option, optionIndex) => (
                  <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                ))}
              </Select>
            ) : field.type === 'tagfields' ? (
              <Select
                mode="multiple"
                style={{ width: '100%' }}
                placeholder="Select tags"
                defaultValue={field.value} // Set defaultValue to the array of selected values
                options={field.tagOptions.map((option, index) => ({
                  label: (
                    <Space>
                      <span aria-label={option.label}  
                        className={`badge-${option.value}`} 
                        style={{display:'block', width:'14px', height:'14px', borderRadius:'50px'}}>
                      </span>
                      {option.label}
                    </Space>
                  ),
                  value: option.value,
                }))}
              />
            ) : field.type === 'datepicker' ? (
              <DatePicker style={{ width: '100%' }} />
            ) : field.type === 'checkbox' ? (
              <Checkbox defaultChecked={field.value || field.defaultValue}>
                {/* {field.label} */}
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
              <Input.TextArea />
            ) : (
              <Input
                type={field.type}
                style={{ width: '100%' }}
                initialvalue={field.defaultValue}
              />
            )}
          </Form.Item>
        </div>
      ))}
    </div>
  </div>
));

const InvoicesForm = () => {
  const [current, setCurrent] = useState(0);
  
  const next = () => {
    setCurrent(current + 1);
  };

  const prev = () => {
    setCurrent(current - 1);
  };
  const items = steps.map((item) => ({ key: item.title, title: item.title }));

  const [form] = Form.useForm();
  

  // const prefixSelector = (
  //   <Form.Item name="prefix" noStyle>
  //     <Select
  //       style={{
  //         width: 70,
  //       }}
  //     >
  //       <Option value="86">+86</Option>
  //       <Option value="87">+87</Option>
  //       <Option value="91">+91</Option>
  //     </Select>
  //   </Form.Item>
  // );
  // const suffixSelector = (
  //   <Form.Item name="suffix" noStyle>
  //     <Select
  //       style={{
  //         width: 70,
  //       }}
  //     >
  //       <Option value="USD">$</Option>
  //       <Option value="CNY">¥</Option>
  //     </Select>
  //   </Form.Item>
  // );
  const [autoCompleteResult, setAutoCompleteResult] = useState([]);
  const onWebsiteChange = (value) => {
    if (!value) {
      setAutoCompleteResult([]);
    } else {
      setAutoCompleteResult(['.com', '.org', '.net', '.ai', '.in', '.co.in'].map((domain) => `${value}${domain}`));
    }
  };
  const websiteOptions = autoCompleteResult.map((website) => ({
    label: website,
    value: website,
  }));
  const onFinish = (values) => {
    console.log('Received values from form:', values);
  
    // Perform further actions here, such as sending values to a server
  };
  return (
    <>
    <Link to="/">
        <Tooltip title="Back" placement="right">
          <Button shape="circle" htmlType="button">
            <ArrowLeftOutlined />
          </Button>
        </Tooltip>
      </Link>
    <Form
      {...formItemLayout}
      form={form}
      name="invoicesform"
      onFinish={onFinish}
      scrollToFirstError
    >
      {/* <Steps current={current} items={steps} size="small" title ="first"/> */}
      <div>{steps[current]}</div>

      <div style={{display:'flex',flexWrap:'wrap'}}>
        
        <EditableTable/>
      </div>

      <div style={{ marginTop: 24 }}>
        {current < steps.length - 1 && (
          <Button type="primary" onClick={next}>
            Next
          </Button>
        )}
        {current === steps.length - 1 && (
          <Button type="primary" htmlType="submit">
            Create
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

    // <Form
    //   {...formItemLayout}
    //   form={form}
    //   name="customerform"
    //   onFinish={onFinish}
    //   scrollToFirstError
    // >

    //   {formFields.map((section, sectionIndex) => (
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
    //               valuePropName="checked" 
    //             >
    //               {field.type === 'dropdown' ? (
    //                 <Select style={{ width: '100%' }} 
    //                 defaultValue={field.defaultValue}
    //                 //suffixIcon={<SearchOutlined />}
    //                 >
    //                   {field.options.map((option, optionIndex) => (
    //                     <Option key={optionIndex} value={option.value}>{option.label}</Option>
    //                   ))}
    //                 </Select>
    //               ) : field.type === 'datepicker' ? (
    //                 <DatePicker style={{ width: '100%' }} />
    //               ) : field.type === 'checkbox' ? (
    //                 <Checkbox defaultChecked={field.defaultValue}>
    //                   {/* {field.label} */}
    //                 </Checkbox>
    //               ) : field.prefixDropdown ? (
    //                 <Input
    //                   addonBefore={
    //                     <Select defaultValue={field.prefixOptions[0].value}>
    //                       {field.prefixOptions.map((option, optionIndex) => (
    //                         <Option key={optionIndex} value={option.value}>{option.label}</Option>
    //                       ))}
    //                     </Select>
    //                   }
    //                   style={{ width: '100%' }}
    //                   defaultValue={field.defaultValue}
    //                 />
    //               ) : field.type === 'textarea' ? (
    //                 <Input.TextArea />
    //               ) : (
    //                 <Input
    //                   type={field.type}
    //                   defaultValue={field.defaultValue}
    //                   style={{
    //                     width: '100%',
    //                   }}
    //                 />
    //               )}
    //             </Form.Item>
    //           </div>
    //         ))}
    //       </div>
    //     </div>
    //   ))}
    //   <Form.Item 
    //     style={{
    //       display:'flex',justifyContent:'center'
    //     }}
    //     // {...tailFormItemLayout}
    //   >
    //     <Button type="primary" htmlType="submit">
    //       Create
    //     </Button>
    //   </Form.Item>

    // </Form>

  //   <Form 
  //     {...formItemLayout}
  //     name="customerform"
  //     onFinish={onFinish}
  //   >
  //   {formFields.map((section, sectionIndex) => (
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
                
  //               <Input
  //                 type={field.rules.type}
  //                 style={{
  //                   width: '100%',
  //                 }}
  //               />
  //             </Form.Item>
  //           </div>
  //         ))}
  //       </div>
  //     </div>
  //   ))}
  // </Form>

    // <Form
    //   {...formItemLayout}
    //   form={form}
    //   name="customerform"
    //   onFinish={onFinish}
    //   initialvalue={{
    //     residence: ['zhejiang', 'hangzhou', 'xihu'],
    //     prefix: '91',
    //   }}
    //   scrollToFirstError
    //   style={{width: '100%'}}
    // >
    //   <Form.Item
    //     name="firstname"
    //     label="First Name"
    //     rules={[
    //       {
    //         type: 'text',
    //         message: 'The input is not valid!',
    //       },
    //       {
    //         required: true,
    //         message: 'Enter your First Name!',
    //       },
    //     ]}
    //   >
    //     <Input />
    //   </Form.Item>
      
    //   <Form.Item
    //     name="lastname"
    //     label="Last Name"
    //     rules={[
    //       {
    //         type: 'text',
    //         message: 'The input is not valid!',
    //       },
    //       {
    //         required: true,
    //         message: 'Enter your Last Name!',
    //       },
    //     ]}
    //   >
    //     <Input />
    //   </Form.Item>

    //   <Form.Item
    //     name="email"
    //     label="E-mail"
    //     rules={[
    //       {
    //         type: 'email',
    //         message: 'The input is not valid E-mail!',
    //       },
    //       {
    //         required: true,
    //         message: 'Please input your E-mail!',
    //       },
    //     ]}
    //   >
    //     <Input />
    //   </Form.Item>
  
    //   <Form.Item
    //     name="phone"
    //     label="Phone Number"
    //     rules={[
    //       {
    //         required: true,
    //         message: 'Please input your phone number!',
    //       },
    //     ]}
    //   >
    //     <Input
    //       type="number"
    //       addonBefore={prefixSelector}
    //       style={{
    //         width: '100%',
    //       }}
    //     />
    //   </Form.Item>

    //   <Form.Item
    //     name="password"
    //     label="Password"
    //     rules={[
    //       {
    //         required: true,
    //         message: 'Please input your password!',
    //       },
    //     ]}
    //     hasFeedback
    //   >
    //     <Input.Password />
    //   </Form.Item>

    //   <Form.Item
    //     name="confirm"
    //     label="Confirm Password"
    //     dependencies={['password']}
    //     hasFeedback
    //     rules={[
    //       {
    //         required: true,
    //         message: 'Please confirm your password!',
    //       },
    //       ({ getFieldValue }) => ({
    //         validator(_, value) {
    //           if (!value || getFieldValue('password') === value) {
    //             return Promise.resolve();
    //           }
    //           return Promise.reject(new Error('The two passwords that you entered do not match!'));
    //         },
    //       }),
    //     ]}
    //   >
    //     <Input.Password />
    //   </Form.Item>

    //   <Form.Item
    //     name="nickname"
    //     label="Nickname"
    //     tooltip="What do you want others to call you?"
    //     rules={[
    //       {
    //         // required: true,
    //         message: 'Please input your nickname!',
    //         whitespace: true,
    //       },
    //     ]}
    //   >
    //     <Input />
    //   </Form.Item>

    //   <Form.Item
    //     name="website"
    //     label="Website"
    //     rules={[
    //       {
    //         required: true,
    //         message: 'Please input website!',
    //       },
    //     ]}
    //   >
    //     <AutoComplete options={websiteOptions} onChange={onWebsiteChange} placeholder="website">
    //       <Input />
    //     </AutoComplete>
    //   </Form.Item>

    //   <Form.Item
    //     name="intro"
    //     label="Intro"
    //     rules={[
    //       {
    //         required: true,
    //         message: 'Please input Intro',
    //       },
    //     ]}
    //   >
    //     <Input.TextArea showCount maxLength={100} />
    //   </Form.Item>

    //   <Form.Item
    //     name="gender"
    //     label="Gender"
    //     rules={[
    //       {
    //         required: true,
    //         message: 'Please select gender!',
    //       },
    //     ]}
    //   >
    //     <Select placeholder="select your gender">
    //       <Option value="male">Male</Option>
    //       <Option value="female">Female</Option>
    //       <Option value="other">Other</Option>
    //     </Select>
    //   </Form.Item>

    //   {/* <Form.Item label="Captcha" extra="We must make sure that your are a human.">
    //     <Row gutter={8}>
    //       <Col span={12}>
    //         <Form.Item
    //           name="captcha"
    //           noStyle
    //           rules={[
    //             {
    //               required: true,
    //               message: 'Please input the captcha you got!',
    //             },
    //           ]}
    //         >
    //           <Input />
    //         </Form.Item>
    //       </Col>
    //       <Col span={12}>
    //         <Button>Get captcha</Button>
    //       </Col>
    //     </Row>
    //   </Form.Item> */}


    //   {/* <Form.Item
    //     name="residence"
    //     label="Habitual Residence"
    //     rules={[
    //       {
    //         type: 'array',
    //         required: true,
    //         message: 'Please select your habitual residence!',
    //       },
    //     ]}
    //   >
    //     <Cascader options={residences} />
    //   </Form.Item> */}

    //   <Form.Item
    //     name="agreement"
    //     valuePropName="checked"
    //     rules={[
    //       {
    //         validator: (_, value) =>
    //           value ? Promise.resolve() : Promise.reject(new Error('Should accept agreement')),
    //       },
    //     ]}
    //     {...tailFormItemLayout}
    //   >
    //     <Checkbox>
    //       I have read the <a href="">agreement</a>
    //     </Checkbox>
    //   </Form.Item>

    //   <Form.Item {...tailFormItemLayout}>
    //     <Button type="primary" htmlType="submit">
    //       Create
    //     </Button>
    //   </Form.Item>
    // </Form>
  );
};
export default InvoicesForm;
