import AppRoutes from "../AppRoutes";
import React, { useState, useEffect, lazy, Suspense } from 'react';
import { getMenu } from "../../API";
import { Layout, Menu, theme, Select } from 'antd';
import { useLocation } from 'react-router-dom';
import { Link } from 'react-router-dom';
import { useContext } from "react";
import { DataContext } from "../../Context/Context";
const { Header, Content } = Layout;
const { SubMenu } = Menu;

const FallbackIcon = lazy(() => import('@ant-design/icons').then(module => ({ default: module.AppstoreOutlined })));



const dynamicImportIcon = (iconName) => {
  return lazy(() =>
    import('@ant-design/icons').then(module => {
      return { default: module[iconName] || module.AppstoreOutlined };
    })
  );
};

const PageContent = () => {
const { menuItem, setMenuItem } = useContext(DataContext);

  const [collapsed, setCollapsed] = useState(false);
  const location = useLocation();

  useEffect(() => {
    const fetchMenu = async () => {
      try {
        const res = await getMenu();
        setMenuItem(res);
      } catch (error) {
        console.error("Error fetching menu items:", error);
      }
    };

    fetchMenu();
  }, []);

  useEffect(() => {
    console.log("menuItem state has been set:", menuItem);
  }, [menuItem]);

  const {
    token: { colorBgContainer },
  } = theme.useToken();

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

  const itemsWithKeys = generateKeys(menuItem);

  const renderSubMenu = (item) => {
    const IconComponent = dynamicImportIcon(item.icon);

    if (item.children && item.children.length > 0) {
      return (
        <SubMenu key={item.key} icon={<Suspense fallback={<FallbackIcon />}><IconComponent /></Suspense>} title={item.label} className='subMenu'>
          {item.children.map(child => {
            const ChildIconComponent = dynamicImportIcon(child.icon);
            return (
              <Menu.Item key={child.key} icon={<Suspense fallback={<FallbackIcon />}><ChildIconComponent /></Suspense>}>
                <Link to={child.path}>
                  {child.label}
                </Link>
              </Menu.Item>
            );
          })}
        </SubMenu>
      );
    } else {
      return (
        <Menu.Item key={item.key} icon={<Suspense fallback={<FallbackIcon />}><IconComponent /></Suspense>}>
          <Link to={item.path}>
            {item.label}
          </Link>
        </Menu.Item>
      );
    }
  };

  return (
    <Layout>
      <Header
        style={{ display: 'flex', width:'100%', alignItems: 'center', position: 'sticky', top: 0, zIndex: 3, margin: 0, padding: 0, background: colorBgContainer }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <span className="demo-logo-vertica" style={{ display: 'flex', padding: '15px' }}>
            <img width="34" height="34" src="https://img.icons8.com/external-flaticons-flat-flat-icons/34/external-infrastructure-gig-economy-flaticons-flat-flat-icons-2.png" alt="external-infrastructure-gig-economy-flaticons-flat-flat-icons-2" />
          </span>
          <Select className="chinchan"
            defaultValue="lucy"
            style={{ whiteSpace: 'nowrap' }}
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
        </div>
        <div>
          <Menu
            mode="horizontal"
            style={{ border: 'none', width: '100%' }}
          >
            {itemsWithKeys.map(item => renderSubMenu(item))}
          </Menu>
        </div>
      </Header>
      <Layout style={{ minHeight: '100%' }}>
        <Layout style={{
          marginLeft: collapsed ? 80 : 200,
          position: 'absolute',
          width: '-webkit-fill-available',
        }}>
          <Content style={{ padding: '2rem' }}>
            {/* <Header/> */}
            <AppRoutes />
          </Content>
        </Layout>
      </Layout>
    </Layout>
  );
};

export default PageContent;
