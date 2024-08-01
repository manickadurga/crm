import React, { useState, useEffect } from 'react';
import { Button, Form, Input, Select, DatePicker, Checkbox } from 'antd';
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from 'axios';
import { getFormfields } from '../../API';
import dayjs from 'dayjs';

// import { Link, useParams, useNavigate } from "react-router-dom";

const { Option } = Select;

const formItemLayout = {
  labelCol: { xs: { span: 24 }, sm: { span: 10 } },
  wrapperCol: { xs: { span: 24 }, sm: { span: 14 } },
};

const tailFormItemLayout = {
  wrapperCol: { xs: { span: 24, offset: 0 }, sm: { span: 16, offset: 8 } },
};

const TasksForm = () => {
  const [form] = Form.useForm();
  const [selectedOption, setSelectedOption] = useState('teams');
  const [teamFormFields, setTeamFormFields] = useState([]);
  const [columns, setColumns] = useState([]);
  const [teamTaskData, setTeamTaskData] = useState(null);
  const [selectedDueDate, setSelectedDueDate] = useState(null);

  const { id } = useParams();

  useEffect(() => {
      getFormfields('Teamtask')
      .then((res) => {
        setTeamFormFields(res);
      })
      .catch((error) => {
        console.error("Error fetching form fields:", error);
      });
  }, []);

  
  useEffect(() => {
    // Fetch team task data by ID
    const fetchTeamTask = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/teamtasks/${id}`);
        console.log('Team task data fetched:', response.data);
        setTeamTaskData(response.data);
      } catch (error) {
        console.error('Error fetching team task:', error);
        if (error.response && error.response.status === 404) {
          console.error('Team task not found.');
        } else {
          console.error('An unexpected error occurred.');
        }
      }
    };

    // Fetch team task data when ID changes
    if (id) {
      fetchTeamTask();
    }

  }, [id]);

  useEffect(() => {
    if (teamTaskData) {
      // Format duedate and invoicedate to Day.js format
      const formattedData = {
        ...teamTaskData,
        duedate: teamTaskData.duedate ? dayjs(teamTaskData.duedate) : null,
        // invoicedate: invoiceData.invoicedate ? dayjs(invoiceData.invoicedate) : null,
      };

      // Set form fields with invoiceData values
      form.setFieldsValue(formattedData);
    }
  }, [teamTaskData, form]);

  useEffect(() => {
    console.log("customerFormFields state has been set:", teamTaskData);
  }, [teamTaskData]);
    

  const onFinish = async (values) => {
    console.log('Received values from form:', values);
    const url = id ? `http://127.0.0.1:8000/teamtasks/${id}` : 'http://127.0.0.1:8000/teamtasks';
    const method = id ? 'put' : 'post';

    try {
      const response = await axios({ method, url, data: values });
      console.log(`${id ? 'Teamtask updated:' : 'Teamtask created:'}`, response.data);
      // Navigate to invoices list or show success message
    } catch (error) {
      console.error(`There was an error ${id ? 'updating' : 'creating'} the teamtask!`, error);
      // Handle error, show error message
    }
  };

  const steps = teamFormFields.map((section, index) => (
    <div key={index}>
      <h3>{section.title}</h3>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '16px' }}>
        {section.fields.map((field, fieldIndex) => (
          <div key={fieldIndex} style={{ width: '100%' }}>
          <Form.Item
                    name={field.name}
                    label={field.label}
                    rules={field.rules}
                    className="form-item"
                  >
                    {field.type === '16' ? (
                      <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                        {field.options.map((option, optionIndex) => (
                          <Option key={optionIndex} value={option.value}>{option.label}</Option>
                        ))}
                      </Select>
                    ) : field.type === 'dayshrsmins' ? (
                      <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                      {field.fields.map((subField, subFieldIndex) => (
                        <Form.Item
                          key={subFieldIndex}
                          name={subField.name}
                          style={{ width: '30%', margin:0 }}
                        >
                          <Input
                            type='number'
                            min={0}
                            placeholder={subField.name.charAt(0).toUpperCase() + subField.name.slice(1)}
                          />
                          <i style={{fontSize:11, float:'right', color:'#888'}}>{subField.name}</i>
                        </Form.Item>
                      ))}
                    </div>
                  )
                    : field.type === 'radiogroup' ? (
                      <Radio.Group
                        options={field.options}
                        defaultValue={field.options[0].value}
                        onChange={handleRadioChange}
                      />
                    ) : field.type === '4' ? (
                      <Input type="number" min={0} />
                    ): field.type === '23' ? (
                      id ? (  
                        <DatePicker
                          style={{ width: '100%' }}
                          value={field.name === 'duedate' ? selectedDueDate : ''}
                          onChange={(date) => field.name === 'duedate' ? setSelectedDueDate(date) : ''}
                        />)
                        :(
                          <DatePicker
                          style={{ width: '100%' }}
                          />
                        )
                    ) : field.type === 'dropdown' ? (
                      <Select style={{ width: '100%' }} 
                        defaultValue={field.defaultValue}>
                        {field.options.map((option, optionIndex) => (
                      <Option key={optionIndex} value={option.value}>{option.label}</Option>
                    ))}
                         </Select>
                       ) : field.type === '33' ? (
                        <Select
                          mode="multiple"
                          style={{ width: '100%' }}
                          placeholder="Select tags"
                          defaultValue={field.defaultValue}
                        >
                          {field.options.map((option, optionIndex) => (
                            <Option key={optionIndex} value={option.value}>
                              <span>
                                <span
                                  aria-label={option.label}
                                  className={`badge-${option.value}`}
                                  style={{
                                    display: 'block',
                                    width: '14px',
                                    height: '14px',
                                    borderRadius: '50px',
                                    backgroundColor: option.color // Using the color from the options array
                                  }}
                                ></span>
                                {option.label}
                              </span>
                            </Option>
                          ))}
                        </Select>
                      ) : field.type === 'dayshrs' ? (
                           <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                           {field.fields.map((subField, subFieldIndex) => (
                            <Form.Item
                             key={subFieldIndex}
                             name={subField.name}
                             style={{ width: '30%', margin:0 }}
                             >
                             <Input
                              type='number'
                              placeholder={subField.name.charAt(0).toUpperCase() + subField.name.slice(1)}
                            />
                           <i style={{fontSize:11, float:'right', color:'#888'}}>{subField.name}</i>
                           </Form.Item>
                            ))}
                       </div>
                      ):(
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

  return (
    <>
      <Link to="/tasks/teams">
        <Button type="link">
          <ArrowLeftOutlined /> Back to Teamtask
        </Button>
      </Link>
      <Form
        {...formItemLayout}
        form={form}
        name="invoicesform"
        onFinish={onFinish}
        scrollToFirstError
      >
        {steps}
        <Form.Item {...tailFormItemLayout}>
          <Button type="primary" htmlType="submit">
            {id ? 'Update' : 'Create'}
          </Button>
        </Form.Item>
      </Form>
    </>
  );
};

export default TasksForm;

