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
    Steps
  } from 'antd';
  import React, { useState } from 'react';
  import { SearchOutlined } from "@ant-design/icons";
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
      title: "Contact Information",
      fields: [
        {
          name: 'contactowner',
          label: 'Contact Owner',
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
          defaultValue: 'Zohodemo',
        },
        {
          name: 'leadsource',
          label: 'Lead Source',
          rules: [
            {
              required: true,
              message: 'Please select an Lead!',
            },
          ],
          type: 'dropdown',
          options: [
            { label: 'None', value: 'none' },
            { label: 'Lead 1', value: 'lead1' },
            { label: 'Lead 2', value: 'lead2' },
            { label: 'Lead 3', value: 'lead3' },
          ],
          defaultValue: 'none',
          value: 'Lead 1'
        },
        {
          name: 'firstname',
          label: 'First Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your First Name!',
            },
          ],
          type: 'text',
          prefixDropdown: true, // Indicates prefix dropdown
          prefixOptions: [
            { label: 'Mr.', value: 'Mr.' },
            { label: 'Mrs.', value: 'Mrs.' },
            { label: 'Miss.', value: 'Miss.' },
          ],
          prefixOptionsValue: 'Miss.',
          defaultValue: 'Big',
          value: 'R B'
        },
        {
          name: 'lastname',
          label: 'Last Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Last Name!',
            },
          ],
          value: 'Choudhry'
        },
        {
          name: 'accountname',
          label: 'Account Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Account Name!',
            },
          ],
        },
        {
          name: 'vendorname',
          label: 'Vendor Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Vendor Name!',
            },
          ],
        },
        {
          name: 'dob',
          label: 'Date of Birth',
          rules: [
            {
              required: true,
              message: 'Please select your Date of Birth!',
            },
          ],
          type: 'datepicker',
        },
        {
          name: 'emailoptout',
          label: 'Email Opt Out',
          type: 'checkbox',
          defaultValue: true,
        },
      ],
    },
    {
      title: "Address Information",
      fields: [
        {
          name: 'mailingstreet',
          label: 'Mailing Street',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Mailing Street!',
            },
          ],
        },
        {
          name: 'otherstreet',
          label: 'Other Street',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Other Street!',
            },
          ],
        },
        {
          name: 'mailingcity',
          label: 'Mailing City',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Mailing City!',
            },
          ],
        },
        {
          name: 'othercity',
          label: 'Other City',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Other City!',
            },
          ],
        },
        {
          name: 'mailingstate',
          label: 'Mailing State',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Mailing State!',
            },
          ],
        },
        {
          name: 'otherstate',
          label: 'Other State',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Other State!',
            },
          ],
        },
        {
          name: 'mailingcountry',
          label: 'Mailing Country',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Mailing Country!',
            },
          ],
        },
        {
          name: 'othercountry',
          label: 'Other Country',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Other Country!',
            },
          ],
        },
      ]
    },
    {
      title: "Description Information",
      fields: [
        {
          name: "description",
          label: "Description",
          type: "textarea"
        }
      ],
    }
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
            >
              {field.type === 'dropdown' ? (
                <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                  {field.options.map((option, optionIndex) => (
                    <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                  ))}
                </Select>
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

  const CustomerForm = () => {
    const [current, setCurrent] = useState(0);

    const next = () => {
      setCurrent(current + 1);
    };

    const prev = () => {
      setCurrent(current - 1);
    };
    const items = steps.map((item) => ({ key: item.title, title: item.title }));

    const [form] = Form.useForm();
    

    const prefixSelector = (
      <Form.Item name="prefix" noStyle>
        <Select
          style={{
            width: 70,
          }}
        >
          <Option value="86">+86</Option>
          <Option value="87">+87</Option>
          <Option value="91">+91</Option>
        </Select>
      </Form.Item>
    );
    const suffixSelector = (
      <Form.Item name="suffix" noStyle>
        <Select
          style={{
            width: 70,
          }}
        >
          <Option value="USD">$</Option>
          <Option value="CNY">¥</Option>
        </Select>
      </Form.Item>
    );
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
<Form
      {...formItemLayout}
      form={form}
      name="customerform"
      onFinish={onFinish}
      scrollToFirstError
    >
      <Steps current={current} items={steps} size="small" title ="first"/>
      <div>{steps[current]}</div>
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
  export default CustomerForm;
  