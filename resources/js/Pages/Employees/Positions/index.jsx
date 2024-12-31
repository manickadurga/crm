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
import { getDataFunction } from "../../../API"; // Adjust API import if needed
import { Link } from "react-router-dom";

const Position = () => {
    const [dataSource, setDataSource] = useState([]);
    const [loading, setLoading] = useState(false);
    const [deleteModalVisible, setDeleteModalVisible] = useState(false);
    const [selectedPosition, setSelectedPosition] = useState(null);
    const [viewMode, setViewMode] = useState("card"); // Default view mode to card

    useEffect(() => {
        fetchPosition();
    }, []);

    const fetchPosition = async (page = 1) => {
        setLoading(true);
        try {
            const res = await getDataFunction("position",page);
            console.log("res", res);
            const positions = res.Data || []; // Adjust based on the actual response
            setDataSource(positions);
        } catch (error) {
            console.error("Error fetching positions data:", error);
            message.error("Failed to fetch position data.");
        } finally {
            setLoading(false);
        }
    };

    const showDeleteModal = () => {
        setDeleteModalVisible(true);
    };

    const handleDelete = () => {
        // Call delete API here, then refresh the list
        message.success("Position deleted successfully!");
        fetchPosition(); // Refresh the list after deletion
        setDeleteModalVisible(false);
        setSelectedPosition(null);
    };

    const onRowClick = (record) => {
        setSelectedPosition((prevSelected) =>
            prevSelected && prevSelected.id === record.id ? null : record
        );
    };

    const renderCards = () => (
        <Row gutter={16}>
            {dataSource.map((item, index) => (
                <Col key={item.id} span={6}>
                    <Card
                        title={item.name}
                        onClick={() => onRowClick(item)}
                        style={{
                            cursor: "pointer",
                            backgroundColor:
                                selectedPosition &&
                                selectedPosition.id === item.id
                                    ? "#f0f0f0"
                                    : "white",
                        }}
                    >
                        {index === 0 ? (
                            <p>CEO</p>
                        ) : index === 1 ? (
                            <p>Website</p>
                        ) : null}

                        {selectedPosition &&
                            selectedPosition.id === item.id && (
                                <>
                                    <p>Role: {item.role}</p>
                                    {/* Add any additional details here */}
                                </>
                            )}
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
                            <Link to="/positions">
                                <Button
                                    shape="circle"
                                    htmlType="button"
                                    size="small"
                                >
                                    <ArrowLeftOutlined />
                                </Button>
                            </Link>
                            <b style={{ fontSize: "18px", marginLeft: "18px" }}>
                                Positions
                            </b>
                        </div>

                        <div
                            style={{
                                display: "flex",
                                fontSize: "18px",
                                marginLeft: "18px",
                                gap: "5px",
                            }}
                        >
                            {selectedPosition && (
                                <div style={{ gap: "2px" }}>
                                    <Link
                                        to={`/positions/view/${selectedPosition.id}`}
                                    >
                                        <Button
                                            type="link"
                                            style={{
                                                marginRight: "2px",
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        >
                                            <EyeOutlined />
                                        </Button>
                                    </Link>
                                    <Link
                                        to={`/positions/edit/${selectedPosition.id}`}
                                    >
                                        <Button
                                            type="link"
                                            style={{
                                                marginRight: "2px",
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        >
                                            <EditOutlined />
                                        </Button>
                                    </Link>
                                    <Button
                                        type="link"
                                        onClick={showDeleteModal}
                                        style={{
                                            marginRight: "2px",
                                            border: "1px solid #ccc",
                                            background: "white",
                                        }}
                                    >
                                        <DeleteOutlined />
                                    </Button>
                                </div>
                            )}

                            <Link to="/positions/createform">
                                <Button
                                    type="primary"
                                    htmlType="button"
                                    icon={<PlusOutlined />}
                                    style={{
                                        marginLeft: "10px",
                                        marginRight: "10px",
                                    }}
                                >
                                    Add
                                </Button>
                            </Link>
                            <Button
                                style={{ marginRight: "10px" }}
                                type={
                                    viewMode === "card" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("card")}
                            >
                                <UnorderedListOutlined />
                            </Button>
                            <Button
                                type={
                                    viewMode === "card" ? "default" : "primary"
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
                        <h4>Total Positions: {dataSource.length}</h4>

                        <Modal
                            title="Confirm Deletion"
                            visible={deleteModalVisible}
                            onOk={handleDelete}
                            onCancel={() => setDeleteModalVisible(false)}
                            okText="Delete"
                            cancelText="Cancel"
                        >
                            <p>
                                Are you sure you want to delete this position?
                            </p>
                        </Modal>
                    </div>
                </Space>
            </Col>
        </Row>
    );
};

export default Position;
