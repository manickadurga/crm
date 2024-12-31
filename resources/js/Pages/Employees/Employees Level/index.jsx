import React, { useState, useEffect } from "react";
import { Space, Button, Modal, message, Card, Row, Col } from "antd";
import {
    EyeOutlined,
    UnorderedListOutlined,
    WindowsOutlined,
    DeleteOutlined,
    EditOutlined,
    ArrowLeftOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import { getDataFunction } from "../../../API";
import { Link } from "react-router-dom";

const Employeelevel = () => {
    const [dataSource, setDataSource] = useState([]);
    const [loading, setLoading] = useState(false);
    const [deleteModalVisible, setDeleteModalVisible] = useState(false);
    const [selectedEmployee, setSelectedEmployee] = useState(null);
    const [viewMode, setViewMode] = useState("card");

    useEffect(() => {
        fetchEmployees();
    }, []);

    const fetchEmployees = async () => {
        setLoading(true);
        try {
            const res = await getDataFunction("employee-level", page);
            const employees = res.Data || [];
            setDataSource(employees);
        } catch (error) {
            message.error("Error fetching employees data.");
            console.error("Error fetching employees data:", error);
        } finally {
            setLoading(false);
        }
    };

    const showDeleteModal = () => {
        setDeleteModalVisible(true);
    };

    const handleDelete = () => {
        message.success("Employee deleted successfully!");
        fetchEmployees();
        setDeleteModalVisible(false);
        setSelectedEmployee(null);
    };

    const onRowClick = (record) => {
        setSelectedEmployee((prevSelected) =>
            prevSelected && prevSelected.id === record.id ? null : record
        );
    };

    const renderCards = () => (
        <Row gutter={16}>
            {dataSource.map((item) => (
                <Col key={item.id} span={6}>
                    <Card
                        title={item.name}
                        onClick={() => onRowClick(item)}
                        style={{
                            cursor: "pointer",
                            backgroundColor:
                                selectedEmployee &&
                                selectedEmployee.id === item.id
                                    ? "#f0f0f0"
                                    : "white",
                        }}
                    >
                        {/* Placeholder for additional card content */}
                    </Card>
                </Col>
            ))}
        </Row>
    );

    return (
        <Row>
            <Col span={24}>
                <Space size={20} direction="vertical" style={{ width: "100%" }}>
                    <div
                        style={{
                            position: "sticky",
                            display: "flex",
                            justifyContent: "space-between",
                            gap: "1rem",
                            top: 0,
                            background: "#fff",
                            zIndex: 1,
                        }}
                    >
                        <div style={{ display: "flex", alignItems: "center" }}>
                            <Link to="/employees">
                                <Button shape="circle" size="small">
                                    <ArrowLeftOutlined />
                                </Button>
                            </Link>
                            <b style={{ fontSize: "18px", marginLeft: "18px" }}>
                                Employees
                            </b>
                        </div>

                        <div
                            style={{
                                display: "flex",
                                alignItems: "center",
                                gap: "5px",
                            }}
                        >
                            {selectedEmployee && (
                                <Space size={2}>
                                    <Link
                                        to={`/employees/view/${selectedEmployee.id}`}
                                    >
                                        <Button
                                            type="link"
                                            icon={<EyeOutlined />}
                                            style={{
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        />
                                    </Link>
                                    <Link
                                        to={`/employees/edit/${selectedEmployee.id}`}
                                    >
                                        <Button
                                            type="link"
                                            icon={<EditOutlined />}
                                            style={{
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        />
                                    </Link>
                                    <Button
                                        type="link"
                                        onClick={showDeleteModal}
                                        icon={<DeleteOutlined />}
                                        style={{
                                            border: "1px solid #ccc",
                                            background: "white",
                                        }}
                                    />
                                </Space>
                            )}

                            <Link to="/employees/createform">
                                <Button
                                    type="primary"
                                    icon={<PlusOutlined />}
                                    style={{ marginLeft: "10px" }}
                                >
                                    Add
                                </Button>
                            </Link>

                            <Button
                                type={
                                    viewMode === "card" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("card")}
                            >
                                <UnorderedListOutlined />
                            </Button>
                            <Button
                                type={
                                    viewMode === "table" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("table")}
                                disabled
                            >
                                <WindowsOutlined />
                            </Button>
                        </div>
                    </div>
                    <div style={{ overflowX: "scroll" }}>
                        {viewMode === "card" ? renderCards() : null}
                        <h4>Total Employees: {dataSource.length}</h4>

                        <Modal
                            title="Confirm Deletion"
                            visible={deleteModalVisible}
                            onOk={handleDelete}
                            onCancel={() => setDeleteModalVisible(false)}
                            okText="Delete"
                            cancelText="Cancel"
                        >
                            <p>
                                Are you sure you want to delete this employee?
                            </p>
                        </Modal>
                    </div>
                </Space>
            </Col>
        </Row>
    );
};

export default Employeelevel;
