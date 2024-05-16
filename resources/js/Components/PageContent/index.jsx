import AppRoutes from "../AppRoutes";
import React, { useState, useEffect } from 'react';
import {
  DesktopOutlined,
  FileOutlined,
  PieChartOutlined,
  TeamOutlined,
  UserOutlined,
  AppstoreOutlined, ShopOutlined, ShoppingCartOutlined,
  FlagOutlined,
} from '@ant-design/icons';
import { Breadcrumb, Layout, Menu, theme, Select } from 'antd';
const { Header, Content, Footer, Sider } = Layout;
import { useLocation, useNavigate } from 'react-router-dom';

const { SubMenu } = Menu;

const items = [
  { path: '/', icon: <AppstoreOutlined />, label: 'Dashboard' },
  { path: '/inventory', icon: <ShopOutlined />, label: 'Inventory',
    children: [
      { path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
      { path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales'}
    ] 
  },
  { icon: <ShoppingCartOutlined />, label: 'Orders', 
    children: [
      { path:'/orders', icon:<ShoppingCartOutlined />, label: 'Purchase' },
      { path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales',
        // children: [
        //   { path: '/salesexport', label: 'Sales Expo'},
        //   { path: '/salesreturn', label: 'Sales Return'}
        // ]
      }
    ] 
  },
  { icon: <UserOutlined />, label: 'Contacts',
    children: [
      { path:'/customers', icon:<ShoppingCartOutlined />, label: 'Customers' },
      { path:'/community', icon:<ShoppingCartOutlined />, label: 'Community'}
    ] 
  },
  { icon: <FlagOutlined />, label: 'Goals',
    children: [
      { path:'/goals', icon:<ShoppingCartOutlined />, label: 'Manage' },
      { path:'/community', icon:<ShoppingCartOutlined />, label: 'Community'}
    ] 
  },
];
// const items = [
//   { key:'1', path: '/', icon: <AppstoreOutlined />, label: 'Dashboard' },
//   { key:'sub1',  path: '/inventory', icon: <ShopOutlined />, label: 'Inventory',
//     children: [
//       { key:'2',  path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
//       { key:'3',  path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales'}
//     ] 
//   },
//   { key:'sub2',  icon: <ShoppingCartOutlined />, label: 'Orders', 
//     children: [
//       { key:'sub3',  path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales',
//         children: [
//           { key:'4',  path: '/salesexport', label: 'Sales Expo'},
//           { key:'5',  path: '/salesreturn', label: 'Sales Return'}
//         ]
//       }
//     ] 
//   },
//   { key:'sub4', icon: <UserOutlined />, label: 'Contacts',
//     children: [
//       { key:'6',  path:'/customers', icon:<ShoppingCartOutlined />, label: 'Customers' },
//       { key:'7', path:'/community', icon:<ShoppingCartOutlined />, label: 'Community'}
//     ] 
//   },
// ];
const generateKeys = (items) => {
  let keyIndex = 1;

  const assignKeys = (items, parentKey = '') => {
    let subcount = 1;
      return items.map(item => {
      const newItem = { ...item };
      if (item.children) {
        newItem.key = `sub${subcount++}`;
        newItem.children = assignKeys(item.children, newItem.key);
      } else {
        newItem.key = `${keyIndex++}`;
      }
      return newItem;
    });
  };

  return assignKeys(items);
};

const itemsWithKeys = generateKeys(items);

console.log(itemsWithKeys);

const PageContent = () => {
  const [collapsed, setCollapsed] = useState(false);
  const location = useLocation();
  const [selectedKeys, setSelectedKeys] = useState("/");

  const {
    token: { colorBgContainer, borderRadiusLG },
  } = theme.useToken();
  useEffect(() => {
    const pathName = location.pathname;
    setSelectedKeys(pathName);
  }, [location.pathname]);

  const navigate = useNavigate();

  const renderSubMenu = (item) => {
    if (item.children && item.children.length > 0) {
      return (
        <SubMenu key={item.key} icon={item.icon} title={item.label} className='subMenu'>
          {item.children.map(child => (
            <Menu.Item key={child.key} onClick={() => navigate(child.path)}>
              {child.label}
            </Menu.Item>
          ))}
        </SubMenu>
      );
    } else {
      return (
        <Menu.Item key={item.key} icon={item.icon} onClick={() => navigate(item.path)}>
          {item.label}
        </Menu.Item>
      );
    }
  };

  return (
    <Layout > 
      <Header 
        style={{display:'flex',alignItems:'center', position:'sticky',top:0, zIndex: 3, padding:0, background: colorBgContainer,}}>
        <span className="demo-logo-vertica" style={{display:'flex',padding:'15px'}}>
          <img width="34" height="34" src="https://img.icons8.com/external-flaticons-flat-flat-icons/34/external-infrastructure-gig-economy-flaticons-flat-flat-icons-2.png" alt="external-infrastructure-gig-economy-flaticons-flat-flat-icons-2"/>
        </span>
        <Select className="chinchan"
    defaultValue="lucy"
    style={{ whiteSpace: 'nowrap' }}
    // onChange={handleChange}
    options={[
      {
        label: <span>manager</span>,
        title: 'Tab Groups',
        options: [
          { label: <span>All Tabs</span>, value: 'alltabs' },
          { label: <span>Xero Bidirectional Integration for ZohoCRM</span>, value: 'xerobizohocrm' },
          { label: <span>Smackcoders</span>, value: 'smackcoders' },
          { label: <span>Freshbooks Integration for ZohoCRM</span>, value: 'fbzohocrm' },
        ],
      },
      {
        label: <span>engineer</span>,
        title: 'engineer',
        options: [
          { label: <span>Chloe</span>, value: 'Chloe' },
          { label: <span>Lucas</span>, value: 'Lucas' },
        ],
      },
    ]}
  />
          <Menu
              // className="SideMenuVertical"
              // mode="vertical"
              // mode="inline"
              mode="horizontal"
              selectedKeys={[selectedKeys]}
              style={{border: 'none'}}
            >
              {itemsWithKeys.map(item => renderSubMenu(item))}
            </Menu>
      </Header>
      <Layout
        style={{
          minHeight: '100vh',
        }}
      > 
        <Sider collapsible collapsed={collapsed} onCollapse={(value) => setCollapsed(value)} theme="light"
        style={{
          overflow: 'auto',
          height: '100vh',
          position: 'fixed',
          left: 0,
          top: '64px',
          bottom: 0,
          zIndex: 1,
          borderRight: '1px solid #eee',
        }}>
          {/* <Menu theme="dark" defaultSelectedKeys={['1']} mode="inline" items={items} /> */}
          <Menu
            // className="SideMenuVertical"
            // mode="vertical"
            mode="inline"
            selectedKeys={[selectedKeys]}
            style={{border: 'none',}}
          >
            {itemsWithKeys.map(item => renderSubMenu(item))}
          </Menu>
        </Sider>
        <Layout style={{ 
          marginLeft: collapsed ? 80 : 200 , 
          position:'absolute',
          width: '-webkit-fill-available', 
          }}>
          <Content style={{ padding: '2rem', }}>
          <AppRoutes /> 
          </Content>
        </Layout>
      </Layout>
    </Layout>
  );
};
export default PageContent;