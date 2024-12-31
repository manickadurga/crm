import React, { useState, useEffect } from 'react';
import { Button, Form, Input, Select, DatePicker, Checkbox,Upload,Radio} from 'antd';
//import {Radio} from 'antd';
import { ArrowLeftOutlined,UploadOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from 'axios';
import {getFormfields} from '../../../API'
// import { getFormfields } from '../../API';
import dayjs from 'dayjs';
import dummyImg from '../../../../../public/assests/img/noprofile.png'

// import { Link, useParams, useNavigate } from "react-router-dom";

const { Option } = Select;

const formItemLayout = {
  labelCol: { xs: { span: 24 }, sm: { span: 10 } },
  wrapperCol: { xs: { span: 24 }, sm: { span: 14 } },
};

const tailFormItemLayout = {
  wrapperCol: { xs: { span: 24, offset: 0 }, sm: { span: 16, offset: 8 } },
};

const EquipmentsForm = () => {
  const [form] = Form.useForm();
  const [selectedOption, setSelectedOption] = useState('teams');
  const [teamFormFields, setTeamFormFields] = useState([]);
  const [columns, setColumns] = useState([]);
  const [equipmentData, setEquipmentData] = useState(null);
  const [selectedDueDate, setSelectedDueDate] = useState(null);
  const [fileList, setFileList] = useState([]);
  const [imgUrl, setImgUrl] = useState(''); 
  const { id } = useParams();
  const [isChecked, setIsChecked] = React.useState(false);

  useEffect(() => {
      getFormfields('Equipments')
      .then((res) => {
        setTeamFormFields(res);
      })
      .catch((error) => {
        console.error("Error fetching form fields:", error);
      });
  }, []);

  
  useEffect(() => {
    // Fetch Equipment data by ID
    const fetchEquipment = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/equipments/${id}`);
        console.log('Equipment data fetched:', response.data);
        setEquipmentData(response.data);
      } catch (error) {
        console.error('Error fetching Equipment:', error);
        if (error.response && error.response.status === 404) {
          console.error('Equipment not found.');
        } else {
          console.error('An unexpected error occurred.');
        }
      }
    };

    // Fetch Equipment data when ID changes
    if (id) {
      fetchEquipment();
    }

  }, [id]);

  useEffect(() => {
    if (equipmentData) {
      // Format duedate and invoicedate to Day.js format
      const formattedData = {
        ...equipmentData,
        duedate: equipmentData.duedate ? dayjs(equipmentData.duedate) : null,
        // invoicedate: invoiceData.invoicedate ? dayjs(invoiceData.invoicedate) : null,
      };

      // Set form fields with invoiceData values
      form.setFieldsValue(formattedData);
    }
  }, [equipmentData, form]);

  useEffect(() => {
    console.log("customerFormFields state has been set:", equipmentData);
  }, [equipmentData]);
    

  const onFinish = async (values) => {
    const transformedValues = {
      ...values,
      auto_approve: values.auto_approve === 'true' ? 1 : 0,
    };

    console.log('Received values from form:', values);
    const url = id ? `http://127.0.0.1:8000/equipments/${id}` : 'http://127.0.0.1:8000/equipments';
    const method = id ? 'put' : 'post';

    try {
      const response = await axios({ method, url, data: values });
      console.log(`${id ? 'Equipments updated:' : 'Equipments created:'}`, response.data);
      // Navigate to invoices list or show success message
    } catch (error) {
      console.error(`There was an error ${id ? 'updating' : 'creating'} the Equipments!`, error);
      // Handle error, show error message
    }
  };
  const convertToPNG = async (imageUrl) => {
    const img = document.createElement('img');
    img.crossOrigin = 'Anonymous';

    return new Promise((resolve, reject) => {
      img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        canvas.toBlob(blob => {
          const reader = new FileReader();
          reader.onloadend = () => {
            resolve(reader.result);
          };
          reader.readAsDataURL(blob);
        }, 'image/png');
      };

      img.onerror = () => {
        reject(new Error('Failed to load image'));
      };

      img.src = imageUrl;
    });
  };
  const handleChange = async (info) => {
    let newFileList = [...info.fileList];
    newFileList = newFileList.slice(-1);

    if (newFileList.length > 0) {
      const file = newFileList[0];
      if (file.originFileObj) {
        // Check file size (maxSize in KB)
        const maxSize = 2048; // 2048 KB = 2 MB
        if (file.originFileObj.size / 1024 > maxSize) {
          message.error('Image must be smaller than 2MB!');
          return;
        }

        const reader = new FileReader();
        reader.onload = async (e) => {
          try {
            const convertedImage = await convertToPNG(e.target.result);
            setImgUrl(convertedImage);
            // Optionally set form field value (if using Form from antd or other form management)
            // form.setFieldsValue({ image: convertedImage });
          } catch (error) {
            console.error('Error converting image:', error);
            setImgUrl(dummyImg); // Reset to dummy image on error
          }
        };
        reader.readAsDataURL(file.originFileObj);
      }
    } else {
      setImgUrl(dummyImg);
    }

    setFileList(newFileList);
  };
  const handleRadioChange = (e) => {
    const newValue = e.target.value === '1' ? 0 : 1; // Toggle between 1 and 0
    form.setFieldsValue({ [field.name]: newValue });
    setSelectedValue(newValue);
    console.log('Selected Value: ', newValue);
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
              valuePropName={field.type === '56' ? 'checked' : 'value'}
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
                  ): field.type === '56' ? (
                    <Checkbox
                      checked={form.getFieldValue(field.name) === 1}
                      onChange={(e) => {
                        const newValue = e.target.checked ? 1 : 0;
                        form.setFieldsValue({ [field.name]: newValue });
                      }}
                    />
    
                    ): field.type === '69' ? (
                        <Upload
                         action="http://127.0.0.1:8000/api/equipments"
                          listType="picture"
                          maxCount={1}
                          fileList={fileList} 
                          onChange={handleChange}
                          beforeUpload={() => false} // Prevent auto-upload
                          showUploadList={false}
                        >
                          <img
                            src={imgUrl || dummyImg}
                            alt="image"
                            style={{
                              width: 200,
                              height: 200,
                              borderRadius: 10,
                            }}
                          />
                          <Button type="button" icon={<UploadOutlined />}>Click to Upload</Button>
                        </Upload>
      
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
      <Link to="/equipments">
        <Button type="link">
          <ArrowLeftOutlined /> Back to Equipments
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

export default EquipmentsForm;
