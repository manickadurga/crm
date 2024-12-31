import React, { useState, useEffect } from "react";
import {
    Button,
    Form,
    Input,
    Select,
    Checkbox,
    Space,
    Tooltip,
    message,
} from "antd";
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from "axios";

const { Option } = Select;

const formItemLayout = {
    labelCol: { xs: { span: 24 }, sm: { span: 8 } },
    wrapperCol: { xs: { span: 24 }, sm: { span: 16 } },
};

const gridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(2, 1fr)",
    gap: "16px",
};

const gridItemStyle = {
    width: "100%",
};

const formFields = [
    {
        title: "Add Position",
        fields: [
            {
                name: "position_name",
                label: "Position Name",
                type: "text",
                rules: [
                    {
                        required: true,
                        message: "Please enter the position name!",
                    },
                ],
            },
            {
                name: "tags",
                label: "Tags",
                type: "tagfields",
                tagOptions: [
                    { label: "Urgent", value: "urgent" },
                    { label: "Important", value: "important" },
                    { label: "Pending", value: "pending" },
                    { label: "Completed", value: "completed" },
                    { label: "Paid", value: "paid" },
                ],
            },
        ],
    },
];

const steps = formFields.map((section) => (
    <div key={section.title}>
        <h3>{section.title}</h3>
        <div style={gridStyle}>
            {section.fields.map((field, index) => (
                <div key={index} style={gridItemStyle}>
                    <Form.Item
                        name={field.name}
                        label={field.label}
                        rules={field.rules}
                        className="form-item"
                    >
                        {field.type === "dropdown" ? (
                            <Select
                                style={{ width: "100%" }}
                                defaultValue={field.defaultValue}
                            >
                                {field.options.map((option, optionIndex) => (
                                    <Option
                                        key={optionIndex}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </Option>
                                ))}
                            </Select>
                        ) : field.type === "tagfields" ? (
                            <Select
                                mode="multiple"
                                style={{ width: "100%" }}
                                placeholder="Select tags"
                                defaultValue={field.value || []}
                                options={field.tagOptions.map((option) => ({
                                    label: (
                                        <Space>
                                            <span
                                                aria-label={option.label}
                                                className={`badge-${option.value}`}
                                                style={{
                                                    display: "block",
                                                    width: "14px",
                                                    height: "14px",
                                                    borderRadius: "50px",
                                                }}
                                            ></span>
                                            {option.label}
                                        </Space>
                                    ),
                                    value: option.value,
                                }))}
                            />
                        ) : field.type === "checkbox" ? (
                            <Checkbox
                                defaultChecked={field.defaultValue || false}
                            />
                        ) : field.prefixDropdown ? (
                            <Input
                                addonBefore={
                                    <Select
                                        defaultValue={
                                            field.prefixOptionsValue ||
                                            field.prefixOptions[0].value
                                        }
                                    >
                                        {field.prefixOptions.map(
                                            (option, optionIndex) => (
                                                <Option
                                                    key={optionIndex}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </Option>
                                            )
                                        )}
                                    </Select>
                                }
                                style={{ width: "100%" }}
                                defaultValue={field.value || ""}
                            />
                        ) : field.suffixDropdown ? (
                            <Input
                                addonAfter={
                                    <Select
                                        defaultValue={
                                            field.suffixOptionsValue ||
                                            field.suffixOptions[0].value
                                        }
                                    >
                                        {field.suffixOptions.map(
                                            (option, optionIndex) => (
                                                <Option
                                                    key={optionIndex}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </Option>
                                            )
                                        )}
                                    </Select>
                                }
                                style={{ width: "100%" }}
                                defaultValue={field.value || ""}
                            />
                        ) : field.type === "textarea" ? (
                            <Input.TextArea
                                rows={4}
                                placeholder="Notes"
                                maxLength={6}
                            />
                        ) : (
                            <Input
                                type={field.type}
                                style={{ width: "100%" }}
                                defaultValue={field.defaultValue || ""}
                            />
                        )}
                    </Form.Item>
                </div>
            ))}
        </div>
    </div>
));

const PositionForm = () => {
    const { id } = useParams();
    const [current, setCurrent] = useState(0);
    const [form] = Form.useForm();
    const [position, setPosition] = useState(null);

    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/positions/${id}`) // Updated API endpoint
                .then((response) => {
                    setPosition(response.data); // Updated state variable name
                    form.setFieldsValue(response.data);
                })
                .catch((error) => {
                    console.error("Error fetching position data:", error);
                });
        }
    }, [id, form]);

    const storePositionData = async (values) => {
        // Updated function name
        try {
            console.log("position data:", values); // Updated log message
            const response = await axios.post(
                "http://127.0.0.1:8000/api/positions", // Updated API endpoint
                values
            );
            console.log("success position data:", response.data); // Updated log message
            message.success("Position stored successfully!"); // Updated message
            return response.data;
        } catch (error) {
            console.error("Error storing position data:", error); // Updated log message
            throw error;
        }
    };

    const updatePositionData = async (id, values) => {
        // Updated function name
        try {
            const response = await axios.put(
                `http://127.0.0.1:8000/api/positions/${id}`, // Updated API endpoint
                values
            );
            message.success("Position updated successfully!"); // Updated message
            return response.data;
        } catch (error) {
            console.error("Error updating position data:", error); // Updated log message
            throw error;
        }
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updatePositionData(id, values); // Updated function call
            } else {
                await storePositionData(values); // Updated function call
            }
        } catch (error) {
            console.error("Operation failed:", error);
        }
    };

    const next = () => {
        setCurrent((prev) => Math.min(prev + 1, steps.length - 1));
    };

    const prev = () => {
        setCurrent((prev) => Math.max(prev - 1, 0));
    };

    return (
        <>
            <Link to="/positions">
                {" "}
                {/* Updated link path */}
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>

            <Form
                {...formItemLayout}
                form={form}
                name="positionForm" // Updated form name
                onFinish={onFinish}
                scrollToFirstError
                style={{ marginTop: "24px" }}
            >
                <div>{steps[current]}</div>

                <div style={{ marginTop: 24 }}>
                    {current < steps.length - 1 && (
                        <Button type="primary" onClick={next}>
                            Next
                        </Button>
                    )}
                    {current === steps.length - 1 && (
                        <Button type="primary" htmlType="submit">
                            {id ? "Update" : "Create"}
                        </Button>
                    )}
                    {current > 0 && (
                        <Button style={{ margin: "0 8px" }} onClick={prev}>
                            Previous
                        </Button>
                    )}
                </div>
            </Form>
        </>
    );
};

export default PositionForm;
