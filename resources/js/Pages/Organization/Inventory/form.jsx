import React, { useState, useEffect } from "react";
import {
    Button,
    Form,
    Input,
    Select,
    Tooltip,
    Upload,
    message,
    Space,
} from "antd";
import {
    ArrowLeftOutlined,
    UploadOutlined,
    EyeOutlined,
    EditOutlined,
    DeleteOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from "axios";
import dummyImg from "../../../../../public/assests/img/noprofile.png"; // Adjust path as needed

const { Option } = Select;

const formItemLayout = {
    labelCol: { xs: { span: 24 }, sm: { span: 8 } },
    wrapperCol: { xs: { span: 24 }, sm: { span: 16 } },
};

const gridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))",
    gap: "20px",
    marginBottom: "20px",
};

const gridItemStyle = {
    display: "flex",
    flexDirection: "column",
};

const formFields = [
    {
        name: "name",
        label: "Name",
        type: "text",
        rules: [{ required: true, message: "Please fill in the item's name!" }],
    },
    {
        name: "category",
        label: "Category",
        type: "select",
        options: ["Electronics", "Furniture", "Clothing", "Books"],
        rules: [{ required: true, message: "Please select a category!" }],
    },
    {
        name: "quantity",
        label: "Quantity",
        type: "number",
        rules: [{ required: true, message: "Please enter the quantity!" }],
    },
    {
        name: "price",
        label: "Price",
        type: "number",
        rules: [{ required: true, message: "Please enter the price!" }],
    },
    { name: "description", label: "Description", type: "textarea" },
];

const steps = [
    {
        title: "Inventory Details",
        fields: formFields,
    },
];

const InventoryForm = () => {
    const { id } = useParams();
    const [form] = Form.useForm();
    const [fileList, setFileList] = useState([]);
    const [inventory, setInventory] = useState(null);
    const [imgUrl, setImgUrl] = useState("");

    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/inventory/${id}`)
                .then((response) => {
                    setInventory(response.data);
                    form.setFieldsValue(response.data);
                    if (response.data.image) {
                        setFileList([
                            {
                                uid: "-1",
                                name: "image.png",
                                status: "done",
                                url: response.data.image,
                            },
                        ]);
                        setImgUrl(response.data.image);
                    }
                })
                .catch((error) => {
                    console.error("Error fetching inventory data:", error);
                });
        }
    }, [id, form]);
    const handlePlusClick = () => {
        message.info("Opening image gallery...");
    };

    const handleChange = (info) => {
        let newFileList = [...info.fileList];
        newFileList = newFileList.slice(-1);

        if (newFileList.length > 0) {
            const file = newFileList[0];
            if (file.originFileObj) {
                const maxSize = 2048; // 2 MB
                if (file.originFileObj.size / 1024 > maxSize) {
                    message.error("Image must be smaller than 2MB!");
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    setImgUrl(e.target.result);
                };
                reader.readAsDataURL(file.originFileObj);
            }
        } else {
            setImgUrl(dummyImg);
        }

        setFileList(newFileList);
    };

    const storeInventoryData = async (values) => {
        try {
            console.log("Sending data to store:", values);
            const response = await axios.post(
                "http://127.0.0.1:8000/api/inventory",
                values
            );
            console.log("Inventory stored successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error storing inventory data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const updateInventoryData = async (id, values) => {
        try {
            console.log("Sending data to update:", values);
            const response = await axios.put(
                `http://127.0.0.1:8000/api/inventory/${id}`,
                values
            );
            console.log("Inventory updated successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error updating inventory data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updateInventoryData(id, values);
            } else {
                await storeInventoryData(values);
            }
            console.log("Operation successful!");
        } catch (error) {
            console.error("Operation failed:", error);
        }
    };

    const renderFormItem = (field) => {
        const { name, label, type, options } = field;

        switch (type) {
            case "select":
                return (
                    <Select placeholder={`Select ${label}`}>
                        {options &&
                            options.map((option, index) => (
                                <Option key={index} value={option}>
                                    {option}
                                </Option>
                            ))}
                    </Select>
                );
            case "textarea":
                return <Input.TextArea />;
            default:
                return <Input />;
        }
    };

    const showDeleteModal = () => {
        // Implement modal logic here
        console.log("Delete button clicked");
    };

    return (
        <>
            <Link to="/inventory">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>

            <Form
                {...formItemLayout}
                form={form}
                name="inventoryForm"
                onFinish={onFinish}
                scrollToFirstError
            >
                <Upload
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
                    <Button type="button" icon={<UploadOutlined />}>
                        Click to Upload
                    </Button>

                    <Button
                        type="link"
                        onClick={handlePlusClick}
                        style={{
                            marginTop: "24px",
                            marginRight: "2px",
                            border: "1px solid #ccc",
                            background: "white",
                            height: "40px",
                            display: "inline-flex", // Changed to inline-flex
                            alignItems: "center",
                            justifyContent: "center", // Centered content horizontally
                        }}
                    >
                        <PlusOutlined />
                    </Button>

                    <Button
                        type="link"
                        style={{
                            marginTop: "24px",
                            marginRight: "2px",
                            border: "1px solid #ccc",
                            background: "white",
                            height: "40px",
                            display: "inline-flex", // Changed to inline-flex
                            alignItems: "center",
                            justifyContent: "center", // Centered content horizontally
                        }}
                    >
                        <EyeOutlined />
                    </Button>

                    <Button
                        type="link"
                        name="edit"
                        style={{
                            marginTop: "24px",
                            marginRight: "2px",
                            border: "1px solid #ccc",
                            background: "white",
                            height: "40px",
                            display: "inline-flex", // Changed to inline-flex
                            alignItems: "center",
                            justifyContent: "center", // Centered content horizontally
                        }}
                    >
                        <EditOutlined />
                    </Button>

                    <Button
                        type="link"
                        onClick={showDeleteModal}
                        style={{
                            marginTop: "24px",
                            marginRight: "2px",
                            border: "1px solid #ccc",
                            background: "white",
                            height: "40px",
                            display: "inline-flex", // Changed to inline-flex
                            alignItems: "center",
                            justifyContent: "center", // Centered content horizontally
                        }}
                    >
                        <DeleteOutlined />
                    </Button>
                </Upload>

                {steps.map((section) => (
                    <div key={section.title}>
                        <h3>{section.title}</h3>
                        <div style={gridStyle}>
                            {section.fields.map((field, index) => (
                                <div key={index} style={gridItemStyle}>
                                    <Form.Item
                                        name={field.name}
                                        label={field.label}
                                        rules={field.rules || []}
                                    >
                                        {renderFormItem(field)}
                                    </Form.Item>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}

                <div style={{ marginTop: 24 }}>
                    <Button type="primary" htmlType="submit">
                        {id ? "Update" : "Create"}
                    </Button>
                </div>
            </Form>
        </>
    );
};

export default InventoryForm;
