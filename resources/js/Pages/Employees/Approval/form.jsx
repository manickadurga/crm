// import { Button, Form, Input, Checkbox, Select, Tooltip } from "antd";
// import React, { useState, useEffect } from "react";
// import { ArrowLeftOutlined } from "@ant-design/icons";
// import { Link, useParams } from "react-router-dom";
// import axios from "axios";

// const { Option } = Select;

// const gridStyle = {
//     display: "grid",
//     gridTemplateColumns: "repeat(2, 1fr)",
//     gap: "16px",
// };

// const gridItemStyle = {
//     display: "flex",
//     flexDirection: "column",
// };

// const ApprovalForm = () => {
//     const { id } = useParams();
//     const [form] = Form.useForm();

//     useEffect(() => {
//         if (id) {
//             axios
//                 .get(`http://127.0.0.1:8000/api/approvals/${id}`)
//                 .then((response) => {
//                     form.setFieldsValue(response.data);
//                 })
//                 .catch((error) => {
//                     console.error("Error fetching approval data:", error);
//                 });
//         }
//     }, [id, form]);

//     const storeApprovalData = async (values) => {
//         try {
//             console.log("Sending data to store:", values);
//             const response = await axios.post(
//                 "http://127.0.0.1:8000/api/approvals",
//                 values
//             );
//             console.log("Approval stored successfully:", response.data);
//             return response.data;
//         } catch (error) {
//             console.error("Error storing approval data:", error);
//             if (error.response && error.response.status === 422) {
//                 console.error("Validation errors:", error.response.data.errors);
//             }
//             throw error;
//         }
//     };

//     const updateApprovalData = async (id, values) => {
//         try {
//             console.log("Sending data to update:", values);
//             const response = await axios.put(
//                 `http://127.0.0.1:8000/api/approvals/${id}`,
//                 values
//             );
//             console.log("Approval updated successfully:", response.data);
//             return response.data;
//         } catch (error) {
//             console.error("Error updating approval data:", error);
//             if (error.response && error.response.status === 422) {
//                 console.error("Validation errors:", error.response.data.errors);
//             }
//             throw error;
//         }
//     };

//     const onFinish = async (values) => {
//         try {
//             if (id) {
//                 await updateApprovalData(id, values);
//             } else {
//                 await storeApprovalData(values);
//             }
//             console.log("Operation successful!");
//         } catch (error) {
//             console.error("Operation failed:", error);
//         }
//     };

//     const formFields = [
//         {
//             title: "Approval Details",
//             fields: [
//                 {
//                     name: "approval_name",
//                     label: "Approval Name",
//                     type: "text",
//                     rules: [
//                         {
//                             required: true,
//                             message: "Please input the approval name!",
//                         },
//                     ],
//                 },
//                 {
//                     name: "min_count",
//                     label: "Minimum Count",
//                     type: "number",
//                 },
//                 {
//                     name: "approval_policy",
//                     label: "Approval Policy",
//                     type: "text",
//                 },

//                 {
//                     type: "radio",
//                     name: "employees",
//                     label: "Employees",
//                     options: [
//                         { label: "Finance", value: "finance" },
//                         { label: "HR", value: "hr" },
//                         { label: "IT", value: "it" },
//                         // Add more options as needed
//                     ],
//                 },
//                 {
//                     type: "radio",
//                     name: "teams",
//                     label: "Teams",

//                     options: [
//                         { label: "Finance Team", value: "finance_team" },
//                         { label: "HR Team", value: "hr_team" },
//                         { label: "IT Team", value: "it_team" },
//                         // Add more options as needed
//                     ],
//                 },
//                 {
//                     name: "status",
//                     label: "Status",
//                     type: "text",
//                 },
//             ],
//         },
//     ];

//     return (
//         <>
//             <Link to="/approvals">
//                 <Tooltip title="Back" placement="right">
//                     <Button shape="circle" htmlType="button">
//                         <ArrowLeftOutlined />
//                     </Button>
//                 </Tooltip>
//             </Link>
//             <Form
//                 form={form}
//                 name="approvalform"
//                 onFinish={onFinish}
//                 scrollToFirstError
//             >
//                 {formFields.map((section, sectionIndex) => (
//                     <div key={sectionIndex}>
//                         <h3>{section.title}</h3>
//                         <div style={gridStyle}>
//                             {section.fields.map((field, fieldIndex) => (
//                                 <div key={fieldIndex} style={gridItemStyle}>
//                                     <Form.Item
//                                         name={field.name}
//                                         label={field.label}
//                                         rules={field.rules}
//                                     >
//                                         {field.type === "checkbox" ? (
//                                             <Checkbox.Group
//                                                 options={field.options}
//                                             />
//                                         ) : field.type === "text" ? (
//                                             <Input type={field.type} />
//                                         ) : field.type === "number" ? (
//                                             <Input type="number" />
//                                         ) : field.type === "date" ? (
//                                             <Input type="date" />
//                                         ) : (
//                                             <Input type={field.type} />
//                                         )}
//                                     </Form.Item>
//                                 </div>
//                             ))}
//                         </div>
//                     </div>
//                 ))}
//                 <div style={{ marginTop: 24 }}>
//                     <Button type="primary" htmlType="submit">
//                         {id ? "Update" : "Create"}
//                     </Button>
//                 </div>
//             </Form>
//         </>
//     );
// };

// export default ApprovalForm;

import {
    Button,
    Form,
    Input,
    Checkbox,
    Radio,
    Tooltip,
    message,
    Select,
    Space,
    DatePicker,
} from "antd";
import React, { useState, useEffect } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from "axios";

const { Option } = Select;

const gridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(2, 1fr)",
    gap: "16px",
};

const gridItemStyle = {
    display: "flex",
    flexDirection: "column",
};

const formFields = [
    {
        title: "Approval Details",
        fields: [
            {
                name: "approval_name",
                label: "Approval Name",
                type: "text",
                rules: [
                    {
                        required: true,
                        message: "Please input the approval name!",
                    },
                ],
            },
            {
                name: "min_count",
                label: "Minimum Count",
                type: "number",
            },
            {
                name: "approval_policy",
                label: "Approval Policy",
                type: "text",
            },
            {
                type: "radio",
                name: "approval_type",
                label: "Approval Type",
                options: [
                    { value: "employee", label: "Employee" },
                    { value: "team", label: "Team" },
                ],
            },
            {
                name: "tags_name",
                label: "Tags",
                type: "tagfields",
                tagOptions: [
                    {
                        label: "Completed",
                        value: "completed",
                    },
                    {
                        label: "Paid",
                        value: "paid",
                    },
                ],
            },
        ],
    },
];

const steps =
    formFields && Array.isArray(formFields)
        ? formFields.map((section) => (
              <div key={section.title}>
                  <h3>{section.title}</h3>
                  <div style={gridStyle}>
                      {section.fields && Array.isArray(section.fields)
                          ? section.fields.map((field, fieldIndex) => (
                                <div key={fieldIndex} style={gridItemStyle}>
                                    <Form.Item
                                        name={field.name}
                                        label={field.label}
                                        rules={field.rules}
                                        className="form-item"
                                    >
                                        {field.type === "dropdown" ? (
                                            <Select
                                                style={{ width: "100%" }}
                                                defaultValue={
                                                    field.defaultValue
                                                }
                                            >
                                                {field.options &&
                                                Array.isArray(field.options)
                                                    ? field.options.map(
                                                          (
                                                              option,
                                                              optionIndex
                                                          ) => (
                                                              <Option
                                                                  key={
                                                                      optionIndex
                                                                  }
                                                                  value={
                                                                      option.value
                                                                  }
                                                              >
                                                                  {option.label}
                                                              </Option>
                                                          )
                                                      )
                                                    : null}
                                            </Select>
                                        ) : field.type === "tagfields" ? (
                                            <Select
                                                mode="multiple"
                                                style={{ width: "100%" }}
                                                placeholder="Select tags"
                                                defaultValue={field.value}
                                                options={
                                                    field.tagOptions &&
                                                    Array.isArray(
                                                        field.tagOptions
                                                    )
                                                        ? field.tagOptions.map(
                                                              (
                                                                  option,
                                                                  index
                                                              ) => ({
                                                                  label: (
                                                                      <Space>
                                                                          <span
                                                                              aria-label={
                                                                                  option.label
                                                                              }
                                                                              className={`badge-${option.value}`}
                                                                              style={{
                                                                                  display:
                                                                                      "block",
                                                                                  width: "14px",
                                                                                  height: "14px",
                                                                                  borderRadius:
                                                                                      "50px",
                                                                              }}
                                                                          ></span>
                                                                          {
                                                                              option.label
                                                                          }
                                                                      </Space>
                                                                  ),
                                                                  value: option.value,
                                                              })
                                                          )
                                                        : []
                                                }
                                            />
                                        ) : field.type === "datepicker" ? (
                                            <DatePicker
                                                style={{ width: "100%" }}
                                            />
                                        ) : field.type === "checkbox" ? (
                                            <Checkbox
                                                defaultChecked={
                                                    field.value ||
                                                    field.defaultValue
                                                }
                                            />
                                        ) : field.type === "radio" ? (
                                            <Radio.Group
                                                onChange={(e) => {
                                                    form.setFieldsValue({
                                                        [field.name]:
                                                            e.target.value,
                                                    });
                                                }}
                                            >
                                                {field.options &&
                                                Array.isArray(field.options)
                                                    ? field.options.map(
                                                          (option) => (
                                                              <Radio
                                                                  key={
                                                                      option.value
                                                                  }
                                                                  value={
                                                                      option.value
                                                                  }
                                                              >
                                                                  <Space>
                                                                      <span
                                                                          aria-label={
                                                                              option.label
                                                                          }
                                                                          className={`badge-${option.value}`}
                                                                          style={{
                                                                              display:
                                                                                  "block",
                                                                              width: "14px",
                                                                              height: "14px",
                                                                              borderRadius:
                                                                                  "50px",
                                                                              backgroundColor:
                                                                                  "#ccc",
                                                                          }}
                                                                      ></span>
                                                                      {
                                                                          option.label
                                                                      }
                                                                  </Space>
                                                              </Radio>
                                                          )
                                                      )
                                                    : null}
                                            </Radio.Group>
                                        ) : (
                                            <Input
                                                type={field.type}
                                                style={{ width: "100%" }}
                                                defaultValue={
                                                    field.defaultValue
                                                }
                                            />
                                        )}
                                    </Form.Item>
                                </div>
                            ))
                          : null}
                  </div>
              </div>
          ))
        : null;

const ApprovalForm = () => {
    const { id } = useParams();
    const [form] = Form.useForm();
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/approvals/${id}`)
                .then((response) => {
                    form.setFieldsValue(response.data);
                })
                .catch((error) => {
                    console.error("Error fetching approval data:", error);
                });
        }
    }, [id, form]);

    const storeApprovalData = async (values) => {
        setLoading(true);
        try {
            const response = await axios.post(
                "http://127.0.0.1:8000/api/approvals",
                values
            );
            message.success("Approval stored successfully!");
            return response.data;
        } catch (error) {
            console.error("Error storing approval data:", error);
            if (error.response && error.response.status === 422) {
                message.error("Validation errors occurred.");
            }
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const updateApprovalData = async (id, values) => {
        setLoading(true);
        try {
            const response = await axios.put(
                `http://127.0.0.1:8000/api/approvals/${id}`,
                values
            );
            message.success("Approval updated successfully!");
            return response.data;
        } catch (error) {
            console.error("Error updating approval data:", error);
            if (error.response && error.response.status === 422) {
                message.error("Validation errors occurred.");
            }
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updateApprovalData(id, values);
            } else {
                await storeApprovalData(values);
            }
        } catch (error) {
            message.error("Operation failed.");
        }
    };

    return (
        <>
            <Link to="/approvals">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>
            <Form
                form={form}
                name="approvalform"
                onFinish={onFinish}
                scrollToFirstError
            >
                {steps}
                <div style={{ marginTop: 24 }}>
                    <Button type="primary" htmlType="submit" loading={loading}>
                        {id ? "Update" : "Create"}
                    </Button>
                </div>
            </Form>
        </>
    );
};

export default ApprovalForm;
